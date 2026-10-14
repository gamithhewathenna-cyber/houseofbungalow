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
('wo_mcup_p1','A Melbourne Cup afternoon at the House, with good food, flowing drinks and the excitement of race day.','whatson_page','Melbourne Cup - paragraph 1','textarea',10),
('wo_mcup_p2','Settle in for the race, stay for the atmosphere and let the afternoon unfold into the evening.','whatson_page','Melbourne Cup - paragraph 2','textarea',10),
('wo_mcup_btn_label','DISCOVER MORE','whatson_page','Melbourne Cup - button label','text',10),
('wo_mcup_btn_url','melbournecup.php','whatson_page','Melbourne Cup - button URL','text',10),
('wo_mcup_image','assets/img/restaurant.jpg','whatson_page','Melbourne Cup - image','image',10)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- If you already ran this migration with the earlier placeholder copy, update it
-- to the final content (safe no-op if you haven't, or already match).
UPDATE `settings` SET `svalue` = 'A Melbourne Cup afternoon at the House, with good food, flowing drinks and the excitement of race day.'
  WHERE `skey` = 'wo_mcup_p1' AND `svalue` = 'Celebrate Melbourne Cup at House of Bungalow - a long lunch at the Restaurant or champagne and canapés Below, the choice is yours.';
UPDATE `settings` SET `svalue` = 'Settle in for the race, stay for the atmosphere and let the afternoon unfold into the evening.'
  WHERE `skey` = 'wo_mcup_p2' AND `svalue` = 'Tuesday, 3 November 2026. Two spaces, two different ways to spend Cup Day.';
UPDATE `settings` SET `svalue` = 'DISCOVER MORE' WHERE `skey` = 'wo_mcup_btn_label' AND `svalue` = 'DISCOVER';
