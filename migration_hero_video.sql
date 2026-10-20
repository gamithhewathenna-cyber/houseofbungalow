-- =====================================================================
-- House of Bungalow — Hero Video migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new "Hero Background Video" field.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('hero_video','','hero','Hero background video (mp4/webm, ideally 1280×720)','video',2)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
