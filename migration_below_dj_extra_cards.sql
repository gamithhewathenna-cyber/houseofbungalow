-- =====================================================================
-- House of Bungalow — Add extra dummy DJ / Line-up cards
-- Run this once via cPanel > phpMyAdmin > SQL tab, to add 4 more
-- placeholder cards (8 total) so the new horizontal scroller on the
-- Below page has enough cards to actually demonstrate scrolling.
-- Guarded to only run if fewer than 8 cards currently exist, so it's
-- safe even if you run it more than once.
-- =====================================================================

INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`link_url`,`image`,`sort`,`active`)
SELECT * FROM (
  SELECT 'below_dj' AS block_type, 'Lorem Ipsum Dolor' AS title, 'Wednesday' AS subtitle, '8:00 PM to 12:00 AM' AS body, '#' AS link_url, 'assets/img/below.jpg' AS image, 5 AS sort, 1 AS active
  UNION ALL SELECT 'below_dj','Lorem Ipsum Dolor','Thursday','8:00 PM to 12:00 AM','#','assets/img/below.jpg',6,1
  UNION ALL SELECT 'below_dj','Lorem Ipsum Dolor','Friday','9:00 PM to 1:00 AM','#','assets/img/below.jpg',7,1
  UNION ALL SELECT 'below_dj','Lorem Ipsum Dolor','Saturday','9:00 PM to 1:00 AM','#','assets/img/below.jpg',8,1
) AS seed
WHERE (SELECT COUNT(*) FROM `blocks` WHERE block_type = 'below_dj') < 8;
