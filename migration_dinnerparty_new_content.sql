-- =====================================================================
-- House of Bungalow — Dinner Party page: new intro content
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database. Updates the heading/subheading/lede lines/paragraph/hours
-- text, and adds a new second paragraph field (dp_p2) — the images and
-- everything else on the page (menu, gallery, guest artists) are
-- untouched.
-- Safe to run more than once.
-- =====================================================================

UPDATE `settings` SET `svalue` = 'Dinner Party At The House'
  WHERE `skey` = 'dp_heading' AND `section` = 'dinnerparty_page';

UPDATE `settings` SET `svalue` = 'Friday & Saturday At The House'
  WHERE `skey` = 'dp_subheading' AND `section` = 'dinnerparty_page';

UPDATE `settings` SET `svalue` = 'Dinner, turned up.'
  WHERE `skey` = 'dp_lede1' AND `section` = 'dinnerparty_page';

UPDATE `settings` SET `svalue` = 'Start with dinner. Stay for everything that comes after.'
  WHERE `skey` = 'dp_lede2' AND `section` = 'dinnerparty_page';

UPDATE `settings` SET `svalue` = 'As the night builds, so does the room. The music lifts. The drinks keep flowing. Tables stay full and dinner starts to feel more like a party.'
  WHERE `skey` = 'dp_p1' AND `section` = 'dinnerparty_page';

UPDATE `settings` SET `svalue` = '**Friday & Saturday** · From 8:00pm'
  WHERE `skey` = 'dp_hours' AND `section` = 'dinnerparty_page';

-- New second paragraph field — only inserted if it doesn't already exist.
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`)
SELECT 'dp_p2', 'House of Bungalow Dinner Party brings together great food, cocktails, DJs and Friday and Saturday night energy - all around the table.', 'dinnerparty_page', 'Paragraph 2', 'textarea', 6
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `skey` = 'dp_p2');
