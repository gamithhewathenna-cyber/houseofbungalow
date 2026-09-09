-- =====================================================================
-- House of Bungalow — Restaurant & Menus page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new Restaurant & Menus page content.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op
-- for settings; menu items / gallery photos are only inserted if the
-- restaurant page has none yet, so re-running won't duplicate them).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('rest_hero_image','assets/img/restaurant.jpg','restaurant','Hero image (poster/fallback)','image',1),
('rest_hero_video','','restaurant','Hero video (mp4/webm, ideally 1280×720)','video',1),
('rest_heading','Restaurant','restaurant','Heading','text',2),
('rest_subheading','Spanish Inspired. Asian Influenced. Sydney Energy.','restaurant','Subheading','text',3),
('rest_lede','The ground-floor Restaurant is the dining heart of House of Bungalow — elevated without feeling formal, social without losing the detail.','restaurant','Lede paragraph (italic)','textarea',4),
('rest_intro_p1','Led by Chef Gianni Moretto, the menu puts Spanish inspiration at the centre, with Asian flavours, techniques and ingredients woven through where they make sense.','restaurant','Paragraph 1','textarea',5),
('rest_intro_p2','Food made for the table, drinks made for staying, and a room that changes as the day turns into night.','restaurant','Paragraph 2','textarea',6),
('rest_intro_btn_label','BOOK A TABLE','restaurant','Button label','text',7),
('rest_intro_btn_url','#','restaurant','Button URL','text',8),
('rest_food_eyebrow','The Food','restaurant','Food — eyebrow label','text',9),
('rest_food_heading','MADE FOR THE TABLE.','restaurant','Food — heading','text',10),
('rest_food_p1','Expect shareable plates, seafood, char, spice, acidity and sauces with real depth. The menu moves from smaller plates into larger dishes built to sit in the middle of the table.','restaurant','Food — paragraph 1','textarea',11),
('rest_food_p2','It is generous, expressive food rather than a formal tasting experience, joined by a considered wine list and cocktails designed to carry the Restaurant from afternoon into night.','restaurant','Food — paragraph 2','textarea',12),
('rest_food_image','assets/img/restaurant.jpg','restaurant','Food — image','image',13),
('rest_menu_heading','Brunch Specials','restaurant','Menu — heading','text',14),
('rest_menu_lede','Lorem ipsum dolor sit amet consectetur. Mattis elementum hac maecenas euismod purus at. Fringilla lacus enim ut semper pretor vitae ultrices. Sit nunc eu urna dolor ac convallis vel. Nec.','restaurant','Menu — subheading','textarea',15),
('rest_room_eyebrow','The Room','restaurant','Room — eyebrow label','text',16),
('rest_room_heading','A ROOM THAT BUILDS WITH THE NIGHT.','restaurant','Room — heading','text',17),
('rest_room_p1','Warm light. Layered materials. Music at the right level. Lunch is relaxed; dinner has more energy.','restaurant','Room — paragraph 1','textarea',18),
('rest_room_p2','The food remains the reason to come, but the room is designed to make you want to stay.','restaurant','Room — paragraph 2','textarea',19),
('rest_room_image','assets/img/restaurant.jpg','restaurant','Room — image','image',20),
('rest_fridaysat_eyebrow','Friday & Saturday','restaurant','Fri/Sat — eyebrow label','text',21),
('rest_fridaysat_heading','DINNER, TURNED UP.','restaurant','Fri/Sat — heading','text',22),
('rest_fridaysat_p1','From 6 PM on Friday and Saturday, the music lifts, drinks keep flowing and the room becomes more social.','restaurant','Fri/Sat — paragraph 1','textarea',23),
('rest_fridaysat_p2','It''s a dinner party atmosphere built around great food, generous hospitality, good music and a table worth staying at.','restaurant','Fri/Sat — paragraph 2','textarea',24),
('rest_fridaysat_btn_label','BOOK A TABLE','restaurant','Fri/Sat — button label','text',25),
('rest_fridaysat_btn_url','#','restaurant','Fri/Sat — button URL','text',26),
('rest_fridaysat_image','assets/img/restaurant.jpg','restaurant','Fri/Sat — image','image',27),
('rest_happyhour_eyebrow','Happy Hour','restaurant','Happy Hour — eyebrow label','text',28),
('rest_happyhour_heading','AN HOUR WORTH STAYING FOR.','restaurant','Happy Hour — heading','text',29),
('rest_happyhour_p1','Cocktails, wine and something from the kitchen as the House moves from afternoon into evening.','restaurant','Happy Hour — paragraph','textarea',30),
('rest_happyhour_hours1','Tuesday · Wednesday · Thursday · Sunday   3:00 pm – 6:30 pm','restaurant','Happy Hour — hours line 1','text',31),
('rest_happyhour_hours2','Friday & Saturday   6:30 pm – 8:30 pm','restaurant','Happy Hour — hours line 2','text',32),
('rest_happyhour_btn_label','VIEW HAPPY HOUR','restaurant','Happy Hour — button label','text',33),
('rest_happyhour_btn_url','#','restaurant','Happy Hour — button URL','text',34),
('rest_happyhour_image','assets/img/restaurant.jpg','restaurant','Happy Hour — image','image',35),
('rest_chef_eyebrow','The Chef','restaurant','Chef — eyebrow label','text',36),
('rest_chef_name','GIANNI MORETTO','restaurant','Chef — name','text',37),
('rest_chef_p1','Chef Gianni Moretto brings more than 12 years of experience across Australia, Spain and Chile to House of Bungalow.','restaurant','Chef — paragraph 1','textarea',38),
('rest_chef_p2','His menu is shaped by Spanish ideas at its core, with Asian techniques woven through Food and Australian produce driving the seasons.','restaurant','Chef — paragraph 2','textarea',39),
('rest_chef_p3','For Gianni, the food and the room belong together — generous plates, strong flavours, good drinks and people around the table.','restaurant','Chef — paragraph 3','textarea',40),
('rest_chef_image','assets/img/restaurant.jpg','restaurant','Chef — image','image',41),
('rest_hours_heading','Restaurant Hours','restaurant','Hours — heading','text',42),
('rest_hours_lunch','Lunch   Tuesday – Sunday   11:00 AM – 3:00 PM','restaurant','Hours — lunch line','text',43),
('rest_hours_dinner','Dinner   Tuesday – Sunday   5:00 PM – 11:00 PM','restaurant','Hours — dinner line','text',44),
('rest_hours_fridaysat','Friday & Saturday Until 11:30 PM','restaurant','Hours — Fri/Sat line','text',45),
('rest_hours_note','Reservations And Walk-Ins Welcome.','restaurant','Hours — note','text',46),
('rest_hours_btn_label','BOOK A TABLE','restaurant','Hours — button label','text',47),
('rest_hours_btn_url','#','restaurant','Hours — button URL','text',48),
('rest_hours_footnote','Walk-Ins Welcome. Book Ahead For Peak Hours Before Committing.','restaurant','Hours — footnote','text',49)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Brunch menu items — only seeded if this menu is currently empty,
-- so re-running this file won't create duplicates.
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`sort`,`active`)
SELECT * FROM (SELECT
    'menu_brunch' AS block_type, 'Greek Omelette (GF)' AS title, '22' AS subtitle, 'Bell Pepper, Spinach, Tomato, PDO Feta Cheese' AS body, 1 AS sort, 1 AS active
  UNION ALL SELECT 'menu_brunch','Avocado Aegean Toast','21','Sourdough, Tomato, Lemon, Arugula, Basil, Shaved Parmesan, Poached Egg',2,1
  UNION ALL SELECT 'menu_brunch','Smoked Salmon Eggs Benedict','26','Hollandaise Sauce, Spinach',3,1
  UNION ALL SELECT 'menu_brunch','Truffle Fries','18','Parmigiano Reggiano, Chive',4,1
  UNION ALL SELECT 'menu_brunch','French Toast','24','Caramelised Sicilian Pistachio',5,1
  UNION ALL SELECT 'menu_brunch','CASA HEOS Gourmet Burger','39','1/3 Beef Patty, Brioche Bun, Smoked Cheddar, Caramelised Onions, Harissa Aioli, French Fries',6,1
  UNION ALL SELECT 'menu_brunch','Farro Island Salmon Burger','27','Brioche Bun, Baby Arugula, Sauce Tartare, French Fries',7,1
  UNION ALL SELECT 'menu_brunch','7oz Grilled Hanger Steak','36','Charred Lemon, Green Harissa',8,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'menu_brunch');

-- Restaurant gallery placeholder photos — same guard, only seeded once.
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`)
SELECT * FROM (SELECT
    'rest_gallery' AS block_type, '' AS title, 'assets/img/cafe.jpg' AS image, 1 AS sort, 1 AS active
  UNION ALL SELECT 'rest_gallery','','assets/img/restaurant.jpg',2,1
  UNION ALL SELECT 'rest_gallery','','assets/img/below.jpg',3,1
  UNION ALL SELECT 'rest_gallery','','assets/img/hero.jpg',4,1
  UNION ALL SELECT 'rest_gallery','','assets/img/building.png',5,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'rest_gallery');
