-- =====================================================================
-- House of Bungalow — Restaurant Hours redesign migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to split each hours line into a bold label + detail text,
-- matching the new "Lunch Tuesday - Sunday: 11:00 AM - 3:00 PM" style.
-- Safe to run more than once.
-- =====================================================================

-- Shorten the existing detail lines (drop the old "Lunch " / "Dinner " /
-- "Friday & Saturday " prefix now that it's shown separately, in bold).
UPDATE `settings` SET `svalue` = 'Tuesday - Sunday: 11:00 AM - 3:00 PM' WHERE `skey` = 'rest_hours_lunch';
UPDATE `settings` SET `svalue` = 'Tuesday - Sunday: 5:00 PM - 11:00 PM' WHERE `skey` = 'rest_hours_dinner';
UPDATE `settings` SET `svalue` = 'Until 11:30 PM' WHERE `skey` = 'rest_hours_fridaysat';

-- Add the new bold-label fields.
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('rest_hours_lunch_label','Lunch','restaurant','Hours — lunch label (bold)','text',43),
('rest_hours_dinner_label','Dinner','restaurant','Hours — dinner label (bold)','text',45),
('rest_hours_fridaysat_label','Friday & Saturday','restaurant','Hours — Fri/Sat label (bold)','text',47)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
