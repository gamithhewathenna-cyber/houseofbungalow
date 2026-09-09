-- =====================================================================
-- House of Bungalow — Footer Disclaimer migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the disclaimer line shown below the footer copyright.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('footer_disclaimer','Disclaimer: Images shown are 3D architectural renderings for illustrative purposes only. Final finishes, furnishings, colours and design details may vary.','footer','Disclaimer (below copyright)','textarea',9)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
