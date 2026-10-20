-- =====================================================================
-- Adds email-based account setup and "Forgot password" for the admin
-- panel:
--   - Adding a new admin (Admin Users > Add an Admin) now asks for an
--     email instead of a password - that person gets an email with a
--     link to set their own password and sign in.
--   - The login page has a "Forgot password?" link that emails a reset
--     link, the same way.
--   - You can sign in with either your username OR your email.
--
-- Each admin needs an email on file for this to work - add yours under
-- Admin Users (or Account, for your own login) after running this.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Not safe to run twice as-is (the ALTER TABLE lines error if the
-- columns/keys already exist) - if you've already run this once, skip it.
-- =====================================================================

ALTER TABLE `admins` ADD COLUMN `email` VARCHAR(190) NULL AFTER `username`;
ALTER TABLE `admins` ADD COLUMN `reset_token` VARCHAR(64) NULL;
ALTER TABLE `admins` ADD COLUMN `reset_token_expires` DATETIME NULL;
ALTER TABLE `admins` ADD UNIQUE KEY `email` (`email`);
ALTER TABLE `admins` ADD UNIQUE KEY `reset_token` (`reset_token`);
ALTER TABLE `admins` MODIFY COLUMN `password_hash` VARCHAR(255) NOT NULL DEFAULT '';
