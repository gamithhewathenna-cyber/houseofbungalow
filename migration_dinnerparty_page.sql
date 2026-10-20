-- =====================================================================
-- House of Bungalow — Dinner Party page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new Dinner Party page content.
-- Note: the Dinner Party menu uses its OWN dataset (block_type = dp_menu_*)
-- separate from the Restaurant page's menu (menu_*) and the Brunch
-- page's menu (brunch_menu_*) — editing one does not affect the others.
-- Menu categories start empty; add items via Admin > Dinner Party > Menu.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op
-- for settings; the gallery is only inserted if it doesn't exist yet).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('dp_hero_image','assets/img/below.jpg','dinnerparty_page','Hero image (poster/fallback)','image',1),
('dp_hero_video','','dinnerparty_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('dp_heading','Dinner Party At The House','dinnerparty_page','Heading','text',2),
('dp_subheading','Where Dinner Turns Into A Night Out.','dinnerparty_page','Subheading','text',3),
('dp_lede1','Friday and Saturday, dinner moves into another gear.','dinnerparty_page','Lede line 1','text',4),
('dp_lede2','Music. Drinks. A room that gets fuller and louder as the night goes on.','dinnerparty_page','Lede line 2','text',5),
('dp_p1','From 6pm, expect a livelier soundtrack, flowing drinks and a night that runs later than usual.','dinnerparty_page','Paragraph','textarea',6),
('dp_hours','**Friday & Saturday:** 6:00 PM – Late','dinnerparty_page','Hours line (use **word** for bold)','text',7),
('dp_btn1_label','VIEW DINNER MENU','dinnerparty_page','Button 1 label','text',8),
('dp_btn1_url','#','dinnerparty_page','Button 1 URL','text',9),
('dp_btn2_label','BOOK A TABLE','dinnerparty_page','Button 2 label','text',10),
('dp_btn2_url','#','dinnerparty_page','Button 2 URL','text',11),
('dp_menu_heading','Discover Our Menus','dinnerparty_page','Menu — heading','text',12),
('dp_menu_lede','','dinnerparty_page','Menu — lede (optional)','textarea',13),
('dp_guest_eyebrow','Guest Artists','dinnerparty_page','Guest Artists — eyebrow label','text',14),
('dp_guest_heading','Live Music Every Weekend','dinnerparty_page','Guest Artists — heading','text',15),
('dp_guest_p1','A rotating line-up of local and international artists plays through the night, adding energy to every dinner party.','dinnerparty_page','Guest Artists — paragraph 1','textarea',16),
('dp_guest_p2','Check our socials each week to see who''s playing.','dinnerparty_page','Guest Artists — paragraph 2','textarea',17),
('dp_guest_btn_label','','dinnerparty_page','Guest Artists — button label (optional)','text',18),
('dp_guest_btn_url','#','dinnerparty_page','Guest Artists — button URL','text',19),
('dp_guest_image','assets/img/below.jpg','dinnerparty_page','Guest Artists — image','image',20)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Gallery — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`)
SELECT * FROM (
  SELECT 'dp_gallery' AS block_type, '' AS title, 'assets/img/below.jpg' AS image, 1 AS sort, 1 AS active
  UNION ALL SELECT 'dp_gallery','','assets/img/restaurant.jpg',2,1
  UNION ALL SELECT 'dp_gallery','','assets/img/cafe.jpg',3,1
  UNION ALL SELECT 'dp_gallery','','assets/img/hero.jpg',4,1
  UNION ALL SELECT 'dp_gallery','','assets/img/building.png',5,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'dp_gallery');
