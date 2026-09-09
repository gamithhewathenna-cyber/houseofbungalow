-- =====================================================================
-- House of Bungalow — Restaurant Hero Video migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add a Hero Video field to the Restaurant page (separate
-- from the Home page's own hero video).
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('rest_hero_video','','restaurant','Hero video (mp4/webm, ideally 1280×720)','video',1)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
