-- =====================================================================
-- House of Bungalow — Website Settings migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new "Website Settings" fields (logos, colour
-- theme, SEO visibility, maintenance mode) to an already-created site.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('logo_white','assets/img/logo-white.png','website','White Logo (header)','image',1),
('logo_colour','assets/img/logo-maroon.png','website','Colour Logo (footer)','image',2),
('theme_cream','#F2E8DE','website','Cream (background)','color',3),
('theme_ink','#545355','website','Ink (body text)','color',4),
('theme_ink_soft','#7d7873','website','Ink Soft','color',5),
('theme_ink_mute','#948f8a','website','Ink Mute','color',6),
('theme_maroon','#620E15','website','Maroon (accent)','color',7),
('theme_dark','#0d0b0a','website','Dark','color',8),
('seo_visible','1','website','Allow search engines to index this site','checkbox',9),
('maintenance_mode','0','website','Enable maintenance mode','checkbox',10)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
