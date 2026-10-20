-- =====================================================================
-- House of Bungalow — Reserve page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new Reserve page content.
-- Also points the header's "RESERVE" nav link at this new page (it
-- previously linked to "#").
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('reserve_hero_heading','Reserve Your Night At The House.','reserve_page','Hero card — heading','text',2),
('reserve_hero_subheading','Restaurant. Happy Hour. Below.','reserve_page','Hero card — subheading','text',3),
('reserve_hero_p1','From long lunches to late nights, book the experience that suits your plans — dinner, drinks, or a VIP table Below.','reserve_page','Hero card — paragraph 1','textarea',4),
('reserve_hero_p2','Walk-ins are always welcome, but booking ahead means your table''s ready when you are.','reserve_page','Hero card — paragraph 2','textarea',5),
('reserve_hero_btn_label','VIEW HAPPY HOUR','reserve_page','Hero card — button label','text',6),
('reserve_hero_btn_url','#happyhour','reserve_page','Hero card — button URL (#happyhour scrolls to the Happy Hour section below)','text',7),
('reserve_heading','Reservations','reserve_page','Reservations — heading','text',8),
('reserve_fridaysat_eyebrow','Friday & Saturday','reserve_page','Fri/Sat — eyebrow label','text',9),
('reserve_fridaysat_heading','Dinner, Turned Up.','reserve_page','Fri/Sat — heading','text',10),
('reserve_fridaysat_p1','From 8 PM on Friday and Saturday, the music lifts, drinks keep flowing and the room becomes more social.','reserve_page','Fri/Sat — paragraph 1','textarea',11),
('reserve_fridaysat_p2','It''s a dinner-party atmosphere built around great food, generous hospitality, good music and a table worth staying at.','reserve_page','Fri/Sat — paragraph 2','textarea',12),
('reserve_fridaysat_btn_label','BOOK A TABLE','reserve_page','Fri/Sat — button label','text',13),
('reserve_fridaysat_btn_url','#','reserve_page','Fri/Sat — button URL','text',14),
('reserve_fridaysat_image','assets/img/restaurant.jpg','reserve_page','Fri/Sat — image','image',15),
('reserve_happyhour_eyebrow','Happy Hour','reserve_page','Happy Hour — eyebrow label','text',16),
('reserve_happyhour_heading','An Hour Worth Staying For.','reserve_page','Happy Hour — heading','text',17),
('reserve_happyhour_p1','Cocktails, wine and something from the kitchen as the House moves from afternoon into evening.','reserve_page','Happy Hour — paragraph','textarea',18),
('reserve_happyhour_hours1','**Tuesday · Wednesday · Thursday · Sunday:** 5:00 PM – 6:30 PM','reserve_page','Happy Hour — hours line 1 (use **word** for bold)','text',19),
('reserve_happyhour_hours2','**Friday & Saturday:** 8:30 PM – 9:30 PM','reserve_page','Happy Hour — hours line 2 (use **word** for bold)','text',20),
('reserve_happyhour_btn_label','VIEW HAPPY HOUR','reserve_page','Happy Hour — button label','text',21),
('reserve_happyhour_btn_url','#','reserve_page','Happy Hour — button URL','text',22),
('reserve_happyhour_image','assets/img/below.jpg','reserve_page','Happy Hour — image','image',23),
('reserve_vip_eyebrow','VIP Tables','reserve_page','VIP Tables — eyebrow label','text',24),
('reserve_vip_heading','Your Table. Your Night.','reserve_page','VIP Tables — heading','text',25),
('reserve_vip_p1','For groups wanting their own space, Below offers a limited number of VIP tables with dedicated service and an elevated bottle-service experience.','reserve_page','VIP Tables — paragraph 1','textarea',26),
('reserve_vip_p2','Birthdays. Celebrations. Big nights. Or no reason at all.','reserve_page','VIP Tables — paragraph 2','textarea',27),
('reserve_vip_btn_label','VIP ENQUIRY','reserve_page','VIP Tables — button label','text',28),
('reserve_vip_btn_url','#','reserve_page','VIP Tables — button URL','text',29),
('reserve_vip_image','assets/img/hero.jpg','reserve_page','VIP Tables — image','image',30)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Hero slider photos — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`)
SELECT * FROM (
  SELECT 'reserve_hero_slide' AS block_type, '' AS title, 'assets/img/cafe.jpg' AS image, 1 AS sort, 1 AS active
  UNION ALL SELECT 'reserve_hero_slide','','assets/img/restaurant.jpg',2,1
  UNION ALL SELECT 'reserve_hero_slide','','assets/img/below.jpg',3,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'reserve_hero_slide');

-- Point the header's RESERVE link at the new page (only if it was never
-- customised away from the original "#" placeholder).
UPDATE `settings` SET `svalue` = 'reserve.php'
  WHERE `skey` = 'nav_link_2_url' AND `section` = 'header' AND `svalue` = '#';
