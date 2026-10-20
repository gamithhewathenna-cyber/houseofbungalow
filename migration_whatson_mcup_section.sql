-- =====================================================================
-- Converts the single "Melbourne Cup" section on the What's On page
-- (whatson.php) into a repeatable LIST of image+text sections, so you
-- can Add, Edit and Delete as many as you like from
-- admin/whatson.php > "Melbourne Cup / Featured Sections" tab.
-- Each one alternates image-left / image-right automatically and sits
-- in the same spot: right after the intro, before "Happy Hour".
--
-- This migrates whatever Melbourne Cup content you already have
-- (heading / eyebrow / paragraphs / button / image) into the FIRST
-- row of the new list, so nothing is lost. If you haven't run the
-- earlier wo_mcup migration at all, it falls back to the same default
-- content that migration used.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once - it only seeds that first row if the
-- new list is still empty, so re-running it will never re-add a row
-- you've since edited or deleted in the admin.
-- =====================================================================

INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`link_url`,`link_url2`,`image`,`sort`,`active`)
SELECT
  'wo_feature',
  COALESCE((SELECT svalue FROM settings WHERE skey = 'wo_mcup_heading'), 'Melbourne Cup'),
  COALESCE((SELECT svalue FROM settings WHERE skey = 'wo_mcup_eyebrow'), ''),
  CONCAT(
    COALESCE((SELECT svalue FROM settings WHERE skey = 'wo_mcup_p1'), 'A Melbourne Cup afternoon at the House, with good food, flowing drinks and the excitement of race day.'),
    '\n\n',
    COALESCE((SELECT svalue FROM settings WHERE skey = 'wo_mcup_p2'), 'Settle in for the race, stay for the atmosphere and let the afternoon unfold into the evening.')
  ),
  COALESCE((SELECT svalue FROM settings WHERE skey = 'wo_mcup_btn_url'), 'melbournecup.php'),
  COALESCE((SELECT svalue FROM settings WHERE skey = 'wo_mcup_btn_label'), 'DISCOVER MORE'),
  COALESCE((SELECT svalue FROM settings WHERE skey = 'wo_mcup_image'), 'assets/img/restaurant.jpg'),
  10, 1
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE `block_type` = 'wo_feature');

-- The old wo_mcup_* settings rows are no longer read by the site (the section
-- now reads from the blocks table above) - they're left in place, unused, so
-- nothing is deleted by accident. You can ignore them.
