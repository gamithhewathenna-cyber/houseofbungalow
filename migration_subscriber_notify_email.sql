-- =====================================================================
-- Adds an optional "notify me on new subscriber" email, editable
-- directly on admin > Subscribers (same pattern as the "Send to" / CC
-- fields now shown on Enquiries, VIP Enquiries and Applications).
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE below is a no-op
-- on repeat runs - existing values are left untouched).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('subscribe_notify_to_email','','subscribers_page','Send to (email address) - optional, leave blank for no notification','text',1),
('subscribe_notify_cc_email','','subscribers_page','CC email (optional - comma-separate for more than one)','text',2)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
