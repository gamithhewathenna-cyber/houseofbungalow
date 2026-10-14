-- =====================================================================
-- Adds a new "Melbourne Cup" section to the What's On page (whatson.php),
-- positioned right before "Happy Hour" - same split layout (copy card on
-- the left, rounded image on the right). Fully editable via
-- admin/whatson.php > "Melbourne Cup" tab, independent of every other
-- section on the page.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE below is a no-op
-- on repeat runs - existing rows are left untouched).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('wo_mcup_eyebrow','','whatson_page','Melbourne Cup - eyebrow label (optional)','text',10),
('wo_mcup_heading','Melbourne Cup','whatson_page','Melbourne Cup - heading','text',10),
('wo_mcup_p1','Celebrate Melbourne Cup at House of Bungalow - a long lunch at the Restaurant or champagne and canapés Below, the choice is yours.','whatson_page','Melbourne Cup - paragraph 1','textarea',10),
('wo_mcup_p2','Tuesday, 3 November 2026. Two spaces, two different ways to spend Cup Day.','whatson_page','Melbourne Cup - paragraph 2','textarea',10),
('wo_mcup_btn_label','DISCOVER','whatson_page','Melbourne Cup - button label','text',10),
('wo_mcup_btn_url','melbournecup.php','whatson_page','Melbourne Cup - button URL','text',10),
('wo_mcup_image','assets/img/restaurant.jpg','whatson_page','Melbourne Cup - image','image',10)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
