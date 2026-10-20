-- =====================================================================
-- House of Bungalow — Reserve page: hero video → image slider migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to replace the Reserve page's single hero image/video with
-- an image slider (with next/prev arrows). The hero video option has
-- been removed from this page entirely.
-- Safe to run more than once (the slide seed is only inserted if none
-- exist yet; the DELETE is a no-op once already run).
-- =====================================================================

-- Seed slider photos — only if none exist yet. Re-uses whatever image was
-- already set as the single hero image, plus two extras to demonstrate
-- the slider; add/remove/reorder any of these from Admin > Reserve > Hero Slides.
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`)
SELECT * FROM (
  SELECT 'reserve_hero_slide' AS block_type, '' AS title,
         COALESCE((SELECT svalue FROM settings WHERE skey = 'reserve_hero_image' AND svalue <> ''), 'assets/img/cafe.jpg') AS image,
         1 AS sort, 1 AS active
  UNION ALL SELECT 'reserve_hero_slide','','assets/img/restaurant.jpg',2,1
  UNION ALL SELECT 'reserve_hero_slide','','assets/img/below.jpg',3,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'reserve_hero_slide');

-- The hero video option no longer exists on this page — remove its setting.
DELETE FROM `settings` WHERE `skey` = 'reserve_hero_video' AND `section` = 'reserve_page';

-- The old single hero image setting is now unused (replaced by the slider).
DELETE FROM `settings` WHERE `skey` = 'reserve_hero_image' AND `section` = 'reserve_page';
