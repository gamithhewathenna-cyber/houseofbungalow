-- =====================================================================
-- Adds Google Workspace / SMTP email settings, under
-- admin > Website Settings > "Email (SMTP)". When switched on, every
-- site email (enquiries, VIP bottle requests, job applications,
-- thank-you replies) sends through that mailbox instead of the web
-- server's own mail() function - much more reliable delivery.
--
-- Off by default (smtp_enabled = 0) so nothing changes until you fill
-- these in, test them (the "Send Test Email" button), and turn it on.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE below is a no-op
-- on repeat runs - existing values, including any you've already
-- entered, are left untouched).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('smtp_enabled','0','website','Send email through a Google Workspace / SMTP account','checkbox',11),
('smtp_host','smtp.gmail.com','website','SMTP host','text',12),
('smtp_port','587','website','SMTP port','text',13),
('smtp_encryption','tls','website','SMTP encryption','text',14),
('smtp_username','','website','Workspace email address','text',15),
('smtp_password','','website','Workspace app password','text',16),
('smtp_from_name','','website','"From" name on outgoing emails','text',17),
('smtp_from_email','','website','"From" email address on outgoing emails','text',18)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
