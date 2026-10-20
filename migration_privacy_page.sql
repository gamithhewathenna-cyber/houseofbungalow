-- =====================================================================
-- New Privacy Policy & Terms page (privacy.php). The full legal text
-- lives directly in the page template - only the heading, "last updated"
-- date and intro paragraph are admin-editable. Also adds the footer
-- "View our Privacy Policy." link.
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE is a no-op).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('privacy_heading','Privacy & Terms','privacy_page','Heading','text',1),
('privacy_updated','October 2026','privacy_page','Last updated (shown under the heading)','text',2),
('privacy_intro','House of Bungalow respects your privacy and is committed to handling personal information responsibly. This page explains how we collect and use personal information and the terms that apply when using our website and online services.','privacy_page','Intro paragraph','textarea',3)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
