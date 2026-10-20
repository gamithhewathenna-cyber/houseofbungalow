-- =====================================================================
-- Adds email one-time-code (OTP) verification to admin login, plus a
-- hard 30-day session cap:
--   - Signing in WITHOUT "Remember me" now requires a 6-digit code
--     emailed to the account, after the password is correct.
--   - Ticking "Remember me" skips the code entirely.
--   - Every session - remembered or not - is force-signed-out after
--     30 days no matter what.
--
-- Each admin needs an email on file for the code to be sent (same
-- requirement as the invite/reset-password emails from before). An
-- account with no email can still sign in by ticking "Remember me".
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Not safe to run twice as-is (the ALTER TABLE lines error if the
-- columns already exist) - if you've already run this once, skip it.
-- =====================================================================

ALTER TABLE `admins` ADD COLUMN `otp_code` VARCHAR(10) NULL;
ALTER TABLE `admins` ADD COLUMN `otp_expires` DATETIME NULL;
ALTER TABLE `admins` ADD COLUMN `otp_attempts` TINYINT(3) NOT NULL DEFAULT 0;
