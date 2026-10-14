-- =====================================================================
-- New Melbourne Cup page (melbournecup.php) - same layout/components as
-- the Brunch page (Hero, Intro, Gallery, Menu, Guest Artists), but with
-- its own completely separate content and backend (admin/melbournecup.php),
-- using mcup_* settings and its own gallery/menu datasets - nothing is
-- shared with the Brunch page. Also added to the site's main mobile menu.
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE / WHERE NOT EXISTS
-- guards below are no-ops on repeat runs).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('mcup_hero_image','assets/img/restaurant.jpg','melbournecup_page','Hero image (poster/fallback)','image',1),
('mcup_hero_video','','melbournecup_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('mcup_heading','Melbourne Cup At The House','melbournecup_page','Heading','text',2),
('mcup_subheading','The Race That Stops A Nation.','melbournecup_page','Subheading','text',3),
('mcup_lede1','Fascinators, frocks and a flutter on the first Tuesday of November.','melbournecup_page','Lede line 1','text',4),
('mcup_lede2','Bubbles, a feast-style menu and the race live on the big screen.','melbournecup_page','Lede line 2','text',5),
('mcup_p1','House of Bungalow marks Melbourne Cup with a long lunch built around bottomless drinks, shared plates and the race itself - because nothing pairs better with a sweepstake than a table full of friends.','melbournecup_page','Paragraph','textarea',6),
('mcup_hours','**Tuesday 3 November:** 11:00 AM - Late','melbournecup_page','Hours line (use **word** for bold)','text',7),
('mcup_btn1_label','VIEW MENU','melbournecup_page','Button 1 label','text',8),
('mcup_btn1_url','#','melbournecup_page','Button 1 URL','text',9),
('mcup_btn2_label','BOOK YOUR TABLE','melbournecup_page','Button 2 label','text',10),
('mcup_btn2_url','reserve.php','melbournecup_page','Button 2 URL','text',11),
('mcup_menu_heading','Discover Our Menus','melbournecup_page','Menu - heading','text',12),
('mcup_menu_lede','','melbournecup_page','Menu - lede (optional)','textarea',13),
('mcup_guest_eyebrow','Live Entertainment','melbournecup_page','Guest Artists - eyebrow label','text',14),
('mcup_guest_heading','Sweeps, Style & A Live Band','melbournecup_page','Guest Artists - heading','text',15),
('mcup_guest_p1','Enter our Melbourne Cup sweep, dress to impress for best-dressed prizes and soak up live entertainment as the Cup unfolds.','melbournecup_page','Guest Artists - paragraph 1','textarea',16),
('mcup_guest_p2','Doors open early - get in before the first race to secure your table.','melbournecup_page','Guest Artists - paragraph 2','textarea',17),
('mcup_guest_btn_label','','melbournecup_page','Guest Artists - button label (optional)','text',18),
('mcup_guest_btn_url','#','melbournecup_page','Guest Artists - button URL','text',19),
('mcup_guest_image','assets/img/below.jpg','melbournecup_page','Guest Artists - image','image',20)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Gallery - only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`)
SELECT * FROM (
  SELECT 'mcup_gallery' AS block_type, '' AS title, 'assets/img/restaurant.jpg' AS image, 1 AS sort, 1 AS active
  UNION ALL SELECT 'mcup_gallery','','assets/img/cafe.jpg',2,1
  UNION ALL SELECT 'mcup_gallery','','assets/img/below.jpg',3,1
  UNION ALL SELECT 'mcup_gallery','','assets/img/hero.jpg',4,1
  UNION ALL SELECT 'mcup_gallery','','assets/img/building.png',5,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_gallery');

-- Menu category - only seeded if none exist yet (starts empty, add items via admin).
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`)
SELECT 'mcup_menu_cat', 'Cup Day Menu', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_menu_cat');
