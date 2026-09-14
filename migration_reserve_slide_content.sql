-- =====================================================================
-- House of Bungalow — Reserve page: per-slide content migration
-- Run this once via cPanel > phpMyAdmin > SQL tab. This supersedes the
-- earlier migration_reserve_hero_slider.sql — run THIS one instead
-- (whether or not you already ran that one, or the original
-- migration_reserve_page.sql — this handles every prior state safely).
--
-- What it does:
--  1. If a hero slide already exists without its own content (from the
--     earlier "image-only" slider), backfills the first slide with the
--     old shared heading/subheading/paragraphs/button.
--  2. If no hero slides exist at all yet, creates 3 slides each with
--     their own heading, subheading, paragraph and button.
--  3. Removes the old shared hero settings (heading/subheading/
--     paragraphs/button/video/image) now that each slide has its own.
-- Safe to run more than once.
-- =====================================================================

-- 1) Backfill the first still-empty slide from the old shared settings.
UPDATE `blocks`
SET
  `title` = COALESCE(NULLIF((SELECT svalue FROM settings WHERE skey = 'reserve_hero_heading'), ''), 'Reserve Your Night At The House.'),
  `subtitle` = COALESCE((SELECT svalue FROM settings WHERE skey = 'reserve_hero_subheading'), 'Restaurant. Happy Hour. Below.'),
  `body` = CONCAT(
      COALESCE((SELECT svalue FROM settings WHERE skey = 'reserve_hero_p1'), 'From long lunches to late nights, book the experience that suits your plans — dinner, drinks, or a VIP table Below.'),
      '\n',
      COALESCE((SELECT svalue FROM settings WHERE skey = 'reserve_hero_p2'), 'Walk-ins are always welcome, but booking ahead means your table''s ready when you are.')
  ),
  `link_url` = COALESCE(NULLIF((SELECT svalue FROM settings WHERE skey = 'reserve_hero_btn_url'), ''), '#happyhour'),
  `link_url2` = COALESCE(NULLIF((SELECT svalue FROM settings WHERE skey = 'reserve_hero_btn_label'), ''), 'VIEW HAPPY HOUR')
WHERE `block_type` = 'reserve_hero_slide' AND (`title` IS NULL OR `title` = '')
ORDER BY `sort` ASC, `id` ASC
LIMIT 1;

-- 2) If there are no hero slides at all yet, create 3 to start with.
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`link_url`,`link_url2`,`image`,`sort`,`active`)
SELECT * FROM (
  SELECT 'reserve_hero_slide' AS block_type,
         'Reserve Your Night At The House.' AS title,
         'Restaurant. Happy Hour. Below.' AS subtitle,
         'From long lunches to late nights, book the experience that suits your plans — dinner, drinks, or a VIP table Below.\nWalk-ins are always welcome, but booking ahead means your table''s ready when you are.' AS body,
         '#happyhour' AS link_url, 'VIEW HAPPY HOUR' AS link_url2, 'assets/img/cafe.jpg' AS image, 1 AS sort, 1 AS active
  UNION ALL SELECT 'reserve_hero_slide','Dinner, Turned Up.','Friday & Saturday','From 8 PM, the music lifts, drinks keep flowing and the room becomes more social.','#','BOOK A TABLE','assets/img/restaurant.jpg',2,1
  UNION ALL SELECT 'reserve_hero_slide','Your Table. Your Night.','VIP Tables At Below','For groups wanting their own space, Below offers a limited number of VIP tables with dedicated service.','#','VIP ENQUIRY','assets/img/below.jpg',3,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'reserve_hero_slide');

-- 3) Remove the old shared hero settings — each slide now carries its own.
DELETE FROM `settings` WHERE `section` = 'reserve_page' AND `skey` IN (
  'reserve_hero_heading', 'reserve_hero_subheading', 'reserve_hero_p1', 'reserve_hero_p2',
  'reserve_hero_btn_label', 'reserve_hero_btn_url', 'reserve_hero_video', 'reserve_hero_image'
);
