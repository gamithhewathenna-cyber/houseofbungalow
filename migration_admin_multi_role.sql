-- =====================================================================
-- Allows one admin account to hold MULTIPLE access roles at once (e.g.
-- "Content Editor" AND "Enquiries" together), and widens the `role`
-- column to fit that. Also improves the Admin Users page UI.
--
-- Existing accounts are unaffected - a single role like 'content' is
-- still valid (it's just treated as a one-item list).
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once.
-- =====================================================================

ALTER TABLE `admins` MODIFY COLUMN `role` VARCHAR(60) NOT NULL DEFAULT 'admin';
