<?php
/**
 * A shared, branded HTML template for every email the site sends -
 * enquiry notifications, thank-you replies, admin invites, password
 * resets, test emails. Table-based layout with inline styles throughout,
 * since that's what actually renders consistently across email clients
 * (Gmail, Outlook, Apple Mail, etc. all strip or mangle a <style> block).
 */

/**
 * An absolute URL for use inside an email (asset() / url() alone return
 * a relative path, which has nothing to resolve against in an inbox).
 */
function email_url(string $path): string
{
    return site_origin() . url($path);
}

function email_asset_url(string $path): string
{
    return site_origin() . asset($path);
}

/**
 * One escaped, line-break-preserving paragraph.
 */
function email_p(string $text, string $color = '#2b2a2c'): string
{
    return '<p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:' . e($color) . ';">' . nl2br(e($text), false) . '</p>';
}

/**
 * A clean "Label: value" details table - e.g. an enquiry's submitted
 * fields. Rows with an empty value are skipped automatically.
 */
function email_details_table(array $rows): string
{
    $out = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;margin:4px 0 20px;border-collapse:collapse;">';
    foreach ($rows as $label => $value) {
        $value = trim((string) $value);
        if ($value === '') {
            continue;
        }
        $out .= '<tr>'
            . '<td style="padding:7px 14px 7px 0;color:#9a9089;white-space:nowrap;vertical-align:top;font-size:13px;">' . e($label) . '</td>'
            . '<td style="padding:7px 0;color:#2b2a2c;border-top:1px solid #efe8de;">' . nl2br(e($value), false) . '</td>'
            . '</tr>';
    }
    $out .= '</table>';
    return $out;
}

/**
 * Wraps $bodyHtml (already-built HTML - use email_p()/email_details_table()
 * above to build it) in the site's branded email layout: logo header,
 * heading, body, an optional button, and a plain footer.
 *
 * $cta: ['label' => ..., 'url' => ...] or null for no button.
 */
function email_layout(string $heading, string $bodyHtml, ?array $cta = null): string
{
    $maroon = e(setting('theme_maroon', '#620E15'));
    $cream  = e(setting('theme_cream', '#F2E8DE'));
    $ink    = e(setting('theme_ink', '#2b2a2c'));
    $logo   = e(email_asset_url(setting('logo_white', 'assets/img/logo-white.png')));
    $site   = e(SITE_NAME);
    $origin = e(site_origin());
    $headingEsc = e($heading);

    $ctaHtml = '';
    if ($cta && !empty($cta['label']) && !empty($cta['url'])) {
        $label = e($cta['label']);
        $url   = e($cta['url']);
        $ctaHtml = '
              <tr>
                <td style="padding:6px 40px 10px;text-align:center;">
                  <a href="' . $url . '" style="display:inline-block;background:' . $maroon . ';color:#ffffff;text-decoration:none;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:bold;letter-spacing:.5px;padding:14px 32px;border-radius:4px;">' . $label . '</a>
                </td>
              </tr>';
    }

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . $site . '</title>
</head>
<body style="margin:0;padding:0;background:' . $cream . ';font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:' . $cream . ';padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" style="max-width:560px;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e7ded2;">
          <tr>
            <td style="background:' . $maroon . ';padding:26px 40px;text-align:center;">
              <img src="' . $logo . '" alt="' . $site . '" style="height:38px;max-width:220px;">
            </td>
          </tr>
          <tr>
            <td style="padding:34px 40px 6px;">
              <h1 style="margin:0 0 18px;font-size:19px;color:' . $ink . ';font-family:Georgia,\'Times New Roman\',serif;font-weight:normal;">' . $headingEsc . '</h1>
              ' . $bodyHtml . '
            </td>
          </tr>' . $ctaHtml . '
          <tr>
            <td style="padding:24px 40px 30px;">
              <hr style="border:none;border-top:1px solid #efe8de;margin:0 0 16px;">
              <p style="margin:0;font-size:11px;color:#9a9089;line-height:1.6;">' . $site . ' &middot; <a href="' . $origin . '" style="color:#9a9089;">' . $origin . '</a></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';
}
