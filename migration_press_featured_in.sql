-- =====================================================================
-- Adds a "Featured In" image grid to the Press & Media page, shown
-- right after the intro section (4 across, wraps to a new row after
-- every 4) - manage it from admin > Press & Media > "Featured In" tab:
-- add/edit/delete entries, each with its own photo, title and link
-- (opens in a new tab).
--
-- Seeds 4 PLACEHOLDER entries (generic title + a reused site photo) so
-- you can see the grid working immediately - replace each one with a
-- real publication logo/photo, title and article link from the admin.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (the WHERE NOT EXISTS guard below means
-- the placeholders are only added if you don't already have any
-- "Featured In" entries - it won't re-add them after you've edited or
-- deleted them).
-- =====================================================================

INSERT INTO `blocks` (`block_type`,`title`,`link_url`,`image`,`sort`,`active`)
SELECT 'press_feature', 'Publication Name', '#', 'assets/img/restaurant.jpg', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE `block_type` = 'press_feature');

INSERT INTO `blocks` (`block_type`,`title`,`link_url`,`image`,`sort`,`active`)
SELECT 'press_feature', 'Publication Name', '#', 'assets/img/below.jpg', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE `block_type` = 'press_feature' AND `sort` = 2);

INSERT INTO `blocks` (`block_type`,`title`,`link_url`,`image`,`sort`,`active`)
SELECT 'press_feature', 'Publication Name', '#', 'assets/img/cafe.jpg', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE `block_type` = 'press_feature' AND `sort` = 3);

INSERT INTO `blocks` (`block_type`,`title`,`link_url`,`image`,`sort`,`active`)
SELECT 'press_feature', 'Publication Name', '#', 'assets/img/hero.jpg', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE `block_type` = 'press_feature' AND `sort` = 4);
