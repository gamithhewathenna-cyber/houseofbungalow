-- =====================================================================
-- House of Bungalow — Popup Booking migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the "Book A Table" full-screen popup.
-- The popup opens automatically whenever a visitor clicks any button
-- labelled exactly "BOOK A TABLE" anywhere on the site — matched by the
-- button's text at runtime, so no page-by-page wiring is needed.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('popup_heading','One Address. Every Mood','popup_booking','Heading','text',1),
('popup_subheading','Book Your Table Today','popup_booking','Subheading','text',2),
('popup_btn1_label','Below','popup_booking','Button 1 label','text',3),
('popup_btn1_url','below.php','popup_booking','Button 1 URL','text',4),
('popup_btn2_label','Restaurant','popup_booking','Button 2 label','text',5),
('popup_btn2_url','restaurant.php','popup_booking','Button 2 URL','text',6)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
