-- =====================================================================
-- House of Bungalow — Brunch page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new Brunch page content.
-- Note: the Brunch menu uses its OWN dataset (block_type = brunch_menu_*)
-- separate from the Restaurant page's menu (menu_*) — editing one does
-- not affect the other, even though both start with the same items.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op
-- for settings; gallery/menu items are only inserted if none exist yet).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('brunch_hero_image','assets/img/cafe.jpg','brunch_page','Hero image (poster/fallback)','image',1),
('brunch_hero_video','','brunch_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('brunch_heading','Weekends At The House','brunch_page','Heading','text',2),
('brunch_subheading','Brunch With Nowhere Else To Be.','brunch_page','Subheading','text',3),
('brunch_lede1','Weekends are made for staying at the table.','brunch_page','Lede line 1','text',4),
('brunch_lede2','Good food. Flowing drinks. Music. Long lunches that turn into longer afternoons.','brunch_page','Lede line 2','text',5),
('brunch_p1','House of Bungalow Bottomless Brunch brings together generous food, good drinks and enough energy to make you forget whatever you had planned next.','brunch_page','Paragraph','textarea',6),
('brunch_hours','**Saturday & Sunday:** 11:00 AM – 3:00 PM','brunch_page','Hours line (use **word** for bold)','text',7),
('brunch_btn1_label','VIEW BRUNCH MENU','brunch_page','Button 1 label','text',8),
('brunch_btn1_url','#','brunch_page','Button 1 URL','text',9),
('brunch_btn2_label','BOOK BRUNCH','brunch_page','Button 2 label','text',10),
('brunch_btn2_url','#','brunch_page','Button 2 URL','text',11),
('brunch_menu_heading','Discover Our Menus','brunch_page','Menu — heading','text',12),
('brunch_menu_lede','','brunch_page','Menu — lede (optional)','textarea',13),
('brunch_guest_eyebrow','Guest Artists','brunch_page','Guest Artists — eyebrow label','text',14),
('brunch_guest_heading','Live Music Every Weekend','brunch_page','Guest Artists — heading','text',15),
('brunch_guest_p1','A rotating line-up of local artists plays through brunch, turning breakfast into an occasion.','brunch_page','Guest Artists — paragraph 1','textarea',16),
('brunch_guest_p2','Check our socials each week to see who''s playing.','brunch_page','Guest Artists — paragraph 2','textarea',17),
('brunch_guest_btn_label','','brunch_page','Guest Artists — button label (optional)','text',18),
('brunch_guest_btn_url','#','brunch_page','Guest Artists — button URL','text',19),
('brunch_guest_image','assets/img/below.jpg','brunch_page','Guest Artists — image','image',20)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Gallery — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`)
SELECT * FROM (
  SELECT 'brunch_gallery' AS block_type, '' AS title, 'assets/img/cafe.jpg' AS image, 1 AS sort, 1 AS active
  UNION ALL SELECT 'brunch_gallery','','assets/img/restaurant.jpg',2,1
  UNION ALL SELECT 'brunch_gallery','','assets/img/below.jpg',3,1
  UNION ALL SELECT 'brunch_gallery','','assets/img/hero.jpg',4,1
  UNION ALL SELECT 'brunch_gallery','','assets/img/building.png',5,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'brunch_gallery');

-- Brunch menu items — own dataset, only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`sort`,`active`)
SELECT * FROM (
  SELECT 'brunch_menu_brunch' AS block_type, 'Greek Omelette (GF)' AS title, '22' AS subtitle, 'Bell Pepper, Spinach, Tomato, PDO Feta Cheese' AS body, 1 AS sort, 1 AS active
  UNION ALL SELECT 'brunch_menu_brunch','Avocado Aegean Toast','21','Sourdough, Tomato, Lemon, Arugula, Basil, Shaved Parmesan, Poached Egg',2,1
  UNION ALL SELECT 'brunch_menu_brunch','Smoked Salmon Eggs Benedict','26','Hollandaise Sauce, Spinach',3,1
  UNION ALL SELECT 'brunch_menu_brunch','Truffle Fries','18','Parmigiano Reggiano, Chive',4,1
  UNION ALL SELECT 'brunch_menu_brunch','French Toast','24','Caramelised Sicilian Pistachio',5,1
  UNION ALL SELECT 'brunch_menu_brunch','CASA NEOS Gourmet Burger','39','1/3 Beef Patty, Brioche Bun, Smoked Cheddar, Caramelised Onions, Harissa Aioli, French Fries',6,1
  UNION ALL SELECT 'brunch_menu_brunch','Farie Island Salmon Burger','27','Brioche Bun, Baby Arugula, Sauce Tartare, French Fries',7,1
  UNION ALL SELECT 'brunch_menu_brunch','7oz Grilled Hanger Steak','36','Charred Lemon, Green Harissa',8,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'brunch_menu_brunch');
