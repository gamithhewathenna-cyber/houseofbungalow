<?php
/**
 * Centralized outgoing mail for the whole site.
 *
 * If SMTP is configured and switched on (admin > Website Settings >
 * Email), every form email (enquiries, VIP bottle requests, job
 * applications, thank-you replies) is sent through that account instead
 * of the shared host's built-in mail() function - this is what you want
 * when sending "as" a Google Workspace address, since Gmail/Workspace
 * won't accept mail claiming to be from your domain unless it's actually
 * sent through an authenticated connection like this.
 *
 * Falls back to mail() automatically if SMTP isn't configured, or if an
 * SMTP attempt fails for any reason - a form submission should never be
 * lost just because an email couldn't be sent.
 *
 * $headers uses the exact same array-of-"Name: value"-line format every
 * call site already builds for PHP's mail(). Pass $smtpError by
 * reference to find out *why* an SMTP attempt failed (e.g. to show it in
 * the admin's "Send Test Email" result) - it's left untouched on success
 * or when SMTP isn't enabled.
 */
function app_mail(string $to, string $subject, string $body, array $headers = [], ?string &$smtpError = null): bool
{
    if (setting('smtp_enabled') === '1') {
        try {
            if (smtp_send($to, $subject, $body, $headers, $smtpError)) {
                return true;
            }
        } catch (Throwable $e) {
            $smtpError = $e->getMessage();
        }
    }
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * A minimal, dependency-free SMTP client (EHLO, STARTTLS/implicit SSL,
 * AUTH LOGIN) - works on any shared host with nothing to install.
 * Returns false on any failure (never throws) and, if $error was passed
 * by reference, fills it in with the reason - usually the exact line the
 * mail server sent back, which is the fastest way to see what's wrong.
 */
function smtp_send(string $to, string $subject, string $body, array $headers, ?string &$error = null): bool
{
    $host = trim(setting('smtp_host'));
    $port = (int) setting('smtp_port', '587');
    $user = trim(setting('smtp_username'));
    $pass = setting('smtp_password');
    $enc  = setting('smtp_encryption', 'tls'); // 'tls' = STARTTLS (587), 'ssl' = implicit TLS (465)

    if ($host === '' || $user === '' || $pass === '') {
        $error = 'SMTP host, username or password is not set.';
        return false;
    }

    $fromEmail = trim(setting('smtp_from_email')) ?: $user;
    $fromName  = trim(setting('smtp_from_name')) ?: SITE_NAME;

    // Pull Cc out of the headers array, so Cc'd addresses get a proper
    // SMTP envelope recipient too.
    $cc = [];
    foreach ($headers as $line) {
        if (stripos($line, 'Cc:') === 0) {
            $cc = array_merge($cc, array_map('trim', explode(',', substr($line, 3))));
        }
    }
    $recipients = array_filter(array_merge([$to], $cc));

    $ehloName = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['HTTP_HOST'] ?? '') ?: 'localhost';
    $transport = ($enc === 'ssl' ? 'ssl://' . $host : $host) . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
    $fp = @stream_socket_client($transport, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        $error = 'Could not connect to ' . $host . ':' . $port . ' (' . $errstr . ').';
        return false;
    }
    stream_set_timeout($fp, 10);

    $read = function () use ($fp) {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break; // "250 " (space, not '-') = final line of the reply
        }
        return $data;
    };
    $send = function (string $cmd) use ($fp) { fwrite($fp, $cmd . "\r\n"); };
    $isCode = function (string $data, string $code) { return (bool) preg_match('/(^|\n)' . $code . '[ -]/', $data); };
    $fail = function (string $reason) use ($fp, &$error) {
        $error = $reason;
        fclose($fp);
        return false;
    };

    $resp = $read();
    if (!$isCode($resp, '220')) { return $fail('Server did not answer as an SMTP server: ' . trim($resp)); }

    $send('EHLO ' . $ehloName);
    $resp = $read();
    if (!$isCode($resp, '250')) { return $fail('EHLO failed: ' . trim($resp)); }

    if ($enc === 'tls') {
        $send('STARTTLS');
        $resp = $read();
        if (!$isCode($resp, '220')) { return $fail('STARTTLS failed: ' . trim($resp)); }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { return $fail('Could not start TLS encryption.'); }
        $send('EHLO ' . $ehloName);
        $resp = $read();
        if (!$isCode($resp, '250')) { return $fail('EHLO (after STARTTLS) failed: ' . trim($resp)); }
    }

    $send('AUTH LOGIN');
    $resp = $read();
    if (!$isCode($resp, '334')) { return $fail('AUTH LOGIN not accepted: ' . trim($resp)); }
    $send(base64_encode($user));
    $resp = $read();
    if (!$isCode($resp, '334')) { return $fail('Username rejected: ' . trim($resp)); }
    $send(base64_encode($pass));
    $resp = $read();
    if (!$isCode($resp, '235')) { return $fail('Login failed - check the username and app password: ' . trim($resp)); }

    $send('MAIL FROM:<' . $fromEmail . '>');
    $resp = $read();
    if (!$isCode($resp, '250')) {
        return $fail(
            'The "From" address (' . $fromEmail . ') was rejected: ' . trim($resp) . '. '
            . 'Google only allows sending from the Workspace address you logged in as, or an address added as a '
            . '"Send mail as" alias in that Gmail account (Settings > Accounts > Send mail as). '
            . 'Either clear the "From" email field so it defaults to ' . $user . ', or add ' . $fromEmail . ' as a verified alias first.'
        );
    }

    foreach ($recipients as $rcpt) {
        if (preg_match('/<([^>]+)>/', $rcpt, $m)) { $rcpt = $m[1]; } // "Name <email>" -> email
        $send('RCPT TO:<' . trim($rcpt) . '>');
        $resp = $read();
        if (!$isCode($resp, '250') && !$isCode($resp, '251')) { return $fail('Recipient ' . trim($rcpt) . ' was rejected: ' . trim($resp)); }
    }

    $send('DATA');
    $resp = $read();
    if (!$isCode($resp, '354')) { return $fail('DATA not accepted: ' . trim($resp)); }

    $msgHeaders = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'To: ' . $to,
        'Subject: ' . $subject,
        'Date: ' . date('r'),
        'MIME-Version: 1.0',
    ];
    $hasContentType = false;
    foreach ($headers as $line) {
        if (stripos($line, 'From:') === 0 || stripos($line, 'To:') === 0) {
            continue; // we set these ourselves, above
        }
        if (stripos($line, 'Content-Type:') === 0) {
            $hasContentType = true;
        }
        $msgHeaders[] = $line;
    }
    if (!$hasContentType) {
        $msgHeaders[] = 'Content-Type: text/plain; charset=UTF-8';
    }

    // SMTP's "end of message" marker is a lone "." on its own line, so any
    // line in the body that starts with "." must be escaped to "..".
    $escapedBody = preg_replace('/^\./m', '..', $body);

    $send(implode("\r\n", $msgHeaders) . "\r\n\r\n" . $escapedBody . "\r\n.");
    $resp = $read();
    if (!$isCode($resp, '250')) {
        return $fail('Message was not accepted: ' . trim($resp));
    }

    $send('QUIT');
    fclose($fp);

    return true;
}
