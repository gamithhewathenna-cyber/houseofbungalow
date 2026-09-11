-- =====================================================================
-- House of Bungalow — Phase 01 Database Schema
-- Import this file via cPanel > phpMyAdmin (select your database first)
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Admin users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(60) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: username = admin / password = admin123
-- (CHANGE THIS after first login from the admin panel)
INSERT INTO `admins` (`username`, `password_hash`) VALUES
('admin', '$2y$10$hXTcgdCzP3.OCqEnPoQ/Ke4cDb/jWhcA/YlYHkMu/TJpFAloo3Y8W');

-- ---------------------------------------------------------------------
-- Key/value content store for simple singular fields
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `skey` VARCHAR(100) NOT NULL,
  `svalue` LONGTEXT NULL,
  `section` VARCHAR(60) NOT NULL DEFAULT 'general',
  `label` VARCHAR(160) NOT NULL DEFAULT '',
  `field_type` VARCHAR(20) NOT NULL DEFAULT 'text',
  `sort` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `skey` (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Repeatable blocks (three spaces cards, footer link groups, brand logos)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blocks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `block_type` VARCHAR(60) NOT NULL,
  `title` VARCHAR(160) NULL,
  `subtitle` VARCHAR(255) NULL,
  `body` TEXT NULL,
  `image` VARCHAR(255) NULL,
  `link_url` VARCHAR(255) NULL,
  `link_url2` VARCHAR(255) NULL,
  `sort` INT(11) NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `block_type` (`block_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Newsletter subscribers (captured from footer form)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- SEED CONTENT (matches the supplied design exactly)
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
-- Header
('nav_link_1_label','VIP','header','Nav link 1 label','text',1),
('nav_link_1_url','#','header','Nav link 1 URL','text',2),
('nav_link_2_label','RESERVE','header','Nav link 2 label','text',3),
('nav_link_2_url','#','header','Nav link 2 URL','text',4),
-- Hero
('hero_image','assets/img/hero.jpg','hero','Hero background image (used as video poster / fallback)','image',1),
('hero_video','','hero','Hero background video (mp4/webm, ideally 1280×720)','video',2),
-- Intro
('intro_heading','One Address. Every Mood.','intro','Intro heading','text',1),
('intro_subheading','Café. Restaurant. Late nights.','intro','Intro subheading','text',2),
('intro_address','65 - 67 Foveaux Street, Surry Hills.','intro','Address line','text',3),
('intro_p1','House of Bungalow brings together a neighbourhood Cafe, an elevated Restaurant and Below, our late-night Basement, under one roof in Surry Hills.','intro','Paragraph 1','textarea',4),
('intro_p2a','Come for coffee, lunch, dinner, cocktails or the night downstairs. Each space stands on its own.','intro','Paragraph 2 line A','textarea',5),
('intro_p2b','Together, they make the House.','intro','Paragraph 2 line B','textarea',6),
('intro_p3','Make yourself at home. We''ll take care of the rest.','intro','Paragraph 3','textarea',7),
('intro_btn1_label','EXPLORE THE HOUSE','intro','Button 1 label','text',8),
('intro_btn1_url','#','intro','Button 1 URL','text',9),
('intro_btn2_label','BOOK A TABLE','intro','Button 2 label','text',10),
('intro_btn2_url','#','intro','Button 2 URL','text',11),
('building_image','assets/img/building.png','intro','Building illustration','image',12),
-- Spaces
('spaces_heading','Three Spaces. One House.','spaces','Section heading','text',1),
('spaces_subheading','Three distinct experiences, each with its own reason to walk through the door.','spaces','Section subheading','textarea',2),
-- Door
('door_heading','The House Starts At The Door.','door','Heading','text',1),
('door_p1','The Restaurant and Below each begin with their own entrance and their own backlit agate staircase - two separate arrival moments, two different moods.','door','Paragraph 1','textarea',2),
('door_p2','From there, lighting, material, colour and sound shift with each space. The idea is simple: every room should feel distinct, but unmistakably part of House of Bungalow.','door','Paragraph 2','textarea',3),
('door_btn_label','STEP INSIDE THE HOUSE','door','Button label','text',4),
('door_btn_url','#','door','Button URL','text',5),
-- Mood
('mood_heading','One Address.Every Mood','mood','Heading','text',1),
('mood_p1','Coffee before work. A lunch that runs long. Dinner with friends. A night downstairs.','mood','Paragraph 1','textarea',2),
('mood_p2','House of Bungalow is built for different reasons to come back - all at the same Surry Hills address.','mood','Paragraph 2','textarea',3),
-- Whats On
('whatson_heading','What''s On','whatson','Heading','text',1),
('whatson_p1','The House changes throughout the week.','whatson','Paragraph 1','textarea',2),
('whatson_p2','Weekend brunch. Happy Hour. Friday and Saturday dinner parties. DJs. Guest artists. Late nights Below.','whatson','Paragraph 2','textarea',3),
('whatson_p3','See what''s happening now and what''s coming next','whatson','Paragraph 3','textarea',4),
('whatson_btn_label','SEE WHAT''S ON','whatson','Button label','text',5),
('whatson_btn_url','#','whatson','Button URL','text',6),
-- Private Events
('events_heading','Private Events','events','Heading','text',1),
('events_p1','Your occasion. Your space. Your mood.','events','Paragraph 1','textarea',2),
('events_p2','From intimate dinners and long lunches to birthdays, cocktail receptions, corporate events and late-night celebrations. House of Bungalow offers different spaces for different kinds of occasions.','events','Paragraph 2','textarea',3),
('events_p3','Take over the Restaurant. Go late in Below. Or talk to us about creating something across the House.','events','Paragraph 3','textarea',4),
('events_btn_label','PRIVATE EVENTS','events','Button label','text',5),
('events_btn_url','#','events','Button URL','text',6),
-- Footer
('footer_address','65 - 67 FOVEAUX STREET, SURRY HILLS NSW 2010, SYDNEY, AUSTRALIA','footer','Address','text',1),
('social_instagram','#','footer','Instagram URL','text',2),
('social_spotify','#','footer','Spotify URL','text',3),
('social_tiktok','#','footer','TikTok URL','text',4),
('newsletter_heading','Stay in the House.','footer','Newsletter heading','text',5),
('newsletter_sub','NEWS, EVENTS, NEW MENUS, GUEST DJS AND WHAT''S HAPPENING NEXT.','footer','Newsletter subtext','text',6),
('newsletter_btn','Join our Newsletters','footer','Newsletter button label','text',7),
('copyright','© HOUSE OF BUNGALOW @2025. ALL RIGHTS RESERVED.','footer','Copyright','text',8),
('footer_disclaimer','Disclaimer: Images shown are 3D architectural renderings for illustrative purposes only. Final finishes, furnishings, colours and design details may vary.','footer','Disclaimer (below copyright)','textarea',9),
-- Website Settings
('logo_white','assets/img/logo-white.png','website','White Logo (header)','image',1),
('logo_colour','assets/img/logo-maroon.png','website','Colour Logo (footer)','image',2),
('theme_cream','#F2E8DE','website','Cream (background)','color',3),
('theme_ink','#545355','website','Ink (body text)','color',4),
('theme_ink_soft','#7d7873','website','Ink Soft','color',5),
('theme_ink_mute','#948f8a','website','Ink Mute','color',6),
('theme_maroon','#620E15','website','Maroon (accent)','color',7),
('theme_dark','#0d0b0a','website','Dark','color',8),
('seo_visible','1','website','Allow search engines to index this site','checkbox',9),
('maintenance_mode','0','website','Enable maintenance mode','checkbox',10);

-- Three Spaces cards
INSERT INTO `blocks` (`block_type`,`title`,`image`,`link_url`,`sort`,`active`) VALUES
('space','CAFÉ','assets/img/cafe.jpg','#',1,1),
('space','RESTAURANT','assets/img/restaurant.jpg','#',2,1),
('space','BELOW','assets/img/below.jpg','#',3,1);

-- Footer navigation links
INSERT INTO `blocks` (`block_type`,`title`,`link_url`,`sort`,`active`) VALUES
('footer_nav','RESTAURANT','#',1,1),
('footer_nav','BELOW','#',2,1),
('footer_nav','CAFÉ','#',3,1),
('footer_nav','WHAT''S ON','#',4,1),
('footer_nav','PRIVATE EVENTS','#',5,1),
('footer_nav','HOUSE COLLECTIVE','#',6,1),
('footer_nav','THE HOUSE','#',7,1),
('footer_nav','PRESS','#',8,1),
('footer_nav','JOIN OUR TEAM','#',9,1),
('footer_nav','CONTACT US','#',10,1);

-- Footer brand/partner logos (text-based marks by default; upload images to replace)
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`) VALUES
('brand','Ilan Society','',1,1),
('brand','lux muse','',2,1),
('brand','Dwelling Place','',3,1),
('brand','Fifty Six','',4,1),
('brand','Cresswell','',5,1),
('brand','Refined Beauty','',6,1);

-- =====================================================================
-- Restaurant & Menus page
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

('rest_menu_heading','Discover Our Menus','restaurant','Menu — heading','text',14),
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
('rest_hours_lunch_label','Lunch','restaurant','Hours — lunch label (bold)','text',43),
('rest_hours_lunch','Tuesday - Sunday: 11:00 AM - 3:00 PM','restaurant','Hours — lunch line','text',44),
('rest_hours_dinner_label','Dinner','restaurant','Hours — dinner label (bold)','text',45),
('rest_hours_dinner','Tuesday - Sunday: 5:00 PM - 11:00 PM','restaurant','Hours — dinner line','text',46),
('rest_hours_fridaysat_label','Friday & Saturday','restaurant','Hours — Fri/Sat label (bold)','text',47),
('rest_hours_fridaysat','Until 11:30 PM','restaurant','Hours — Fri/Sat line','text',48),
('rest_hours_note','Reservations And Walk-Ins Welcome.','restaurant','Hours — note','text',46),
('rest_hours_btn_label','BOOK A TABLE','restaurant','Hours — button label','text',47),
('rest_hours_btn_url','#','restaurant','Hours — button URL','text',48),
('rest_hours_footnote','Walk-Ins Welcome. Book Ahead For Peak Hours Before Committing.','restaurant','Hours — footnote','text',49);

-- Restaurant menu categories (admin can add/rename/remove — see admin/restaurant.php)
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Brunch',1,1);
SET @rest_cat_brunch := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Drink Menu',2,1);
SET @rest_cat_dinner := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Desert',3,1);
SET @rest_cat_dessert := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Cocktails',4,1);
SET @rest_cat_cocktails := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Wine',5,1);
SET @rest_cat_wine := LAST_INSERT_ID();

-- Brunch category menu items (other categories start empty — add via admin)
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`sort`,`active`) VALUES
(CONCAT('rest_menu_item_', @rest_cat_brunch),'Greek Omelette (GF)','22','Bell Pepper, Spinach, Tomato, PDO Feta Cheese',1,1),
(CONCAT('rest_menu_item_', @rest_cat_brunch),'Avocado Aegean Toast','21','Sourdough, Tomato, Lemon, Arugula, Basil, Shaved Parmesan, Poached Egg',2,1),
(CONCAT('rest_menu_item_', @rest_cat_brunch),'Smoked Salmon Eggs Benedict','26','Hollandaise Sauce, Spinach',3,1),
(CONCAT('rest_menu_item_', @rest_cat_brunch),'Truffle Fries','18','Parmigiano Reggiano, Chive',4,1),
(CONCAT('rest_menu_item_', @rest_cat_brunch),'French Toast','24','Caramelised Sicilian Pistachio',5,1),
(CONCAT('rest_menu_item_', @rest_cat_brunch),'CASA HEOS Gourmet Burger','39','1/3 Beef Patty, Brioche Bun, Smoked Cheddar, Caramelised Onions, Harissa Aioli, French Fries',6,1),
(CONCAT('rest_menu_item_', @rest_cat_brunch),'Farro Island Salmon Burger','27','Brioche Bun, Baby Arugula, Sauce Tartare, French Fries',7,1),
(CONCAT('rest_menu_item_', @rest_cat_brunch),'7oz Grilled Hanger Steak','36','Charred Lemon, Green Harissa',8,1);

-- Restaurant gallery (placeholder photos — replace via admin)
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`) VALUES
('rest_gallery','','assets/img/cafe.jpg',1,1),
('rest_gallery','','assets/img/restaurant.jpg',2,1),
('rest_gallery','','assets/img/below.jpg',3,1),
('rest_gallery','','assets/img/hero.jpg',4,1),
('rest_gallery','','assets/img/building.png',5,1);

-- =====================================================================
-- Below page
-- =====================================================================
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('below_hero_image','assets/img/below.jpg','below','Hero image (poster/fallback)','image',1),
('below_hero_video','','below','Hero video (mp4/webm, ideally 1280×720)','video',1),
('below_heading','Below','below','Heading','text',2),
('below_subheading','The Basement. Where The Night Takes Over.','below','Subheading','text',3),
('below_lede','Below Foveaux Street is an intimate late-night room built around DJs, cocktails and the energy of being close to the music.','below','Lede paragraph (italic)','textarea',4),
('below_intro_p1','Come early for a drink, stay when the DJ takes over, or make the night your own with one of a limited number of VIP tables.','below','Intro — paragraph 1','textarea',5),
('below_intro_p2','Guest artists. Curated nights. Late finishes.','below','Intro — paragraph 2','textarea',6),
('below_intro_btn1_label','WHAT''S ON','below','Intro — button 1 label','text',7),
('below_intro_btn1_url','#','below','Intro — button 1 URL','text',8),
('below_intro_btn2_label','VIP TABLES','below','Intro — button 2 label','text',9),
('below_intro_btn2_url','#','below','Intro — button 2 URL','text',10),

('below_cocktails_heading','From Cocktails To Late Night','below','Cocktails — heading','text',11),
('below_cocktails_p1','Below opens from 5 PM as a cocktail lounge - somewhere to begin the evening, meet for a drink or continue the night after dinner.','below','Cocktails — paragraph 1 (lede)','textarea',12),
('below_cocktails_p2','From **8:30 PM**, the DJ takes over.','below','Cocktails — paragraph 2 (use **word** for bold)','textarea',13),
('below_cocktails_p3','The lights shift, the music builds and the room changes with it.','below','Cocktails — paragraph 3','textarea',14),
('below_cocktails_hours','**Wednesday – Sunday** · From 5 PM until late.','below','Cocktails — hours line (use **word** for bold)','text',15),
('below_cocktails_image1','assets/img/below.jpg','below','Cocktails — image 1','image',16),
('below_cocktails_image2','assets/img/below.jpg','below','Cocktails — image 2','image',17),
('below_cocktails_image3','assets/img/below.jpg','below','Cocktails — image 3','image',18),

('below_music_heading','Music','below','Music — heading','text',19),
('below_music_lede','Five nights. Five different moods. Music sits at the centre of Below, with each night shaped around its own sound and audience.','below','Music — lede paragraph','textarea',20),
('below_music_btn1_label','BOOK A TABLE','below','Music — button 1 label','text',21),
('below_music_btn1_url','#','below','Music — button 1 URL','text',22),
('below_music_btn2_label','BOOK BOTTLE SERVICE','below','Music — button 2 label','text',23),
('below_music_btn2_url','#','below','Music — button 2 URL','text',24),

('below_lineup_image','assets/img/below.jpg','below','Line-up — background image','image',25),
('below_lineup_heading','See This Week''s Line-Up','below','Line-up — heading','text',26),
('below_lineup_p1','Five nights, each with its own sound. See this week''s DJs, guest artists, special events and late-night line-up.','below','Line-up — paragraph','textarea',27),

('below_guest_eyebrow','Guest Artists','below','Guest Artists — eyebrow label','text',28),
('below_guest_heading','INTERNATIONAL NAMES. INTIMATE NIGHTS. ONLY BELOW.','below','Guest Artists — heading','text',29),
('below_guest_p1','Selected nights bring guest and international artists into Below - chosen for the sound, the room and the night, not simply the size of the name.','below','Guest Artists — paragraph 1','textarea',30),
('below_guest_p2','A smaller room means you''re closer to the artist, the music and the energy around you.','below','Guest Artists — paragraph 2','textarea',31),
('below_guest_btn_label','UPCOMING ARTISTS','below','Guest Artists — button label','text',32),
('below_guest_btn_url','#','below','Guest Artists — button URL','text',33),
('below_guest_image','assets/img/below.jpg','below','Guest Artists — image','image',34),

('below_vip_eyebrow','VIP Tables','below','VIP Tables — eyebrow label','text',35),
('below_vip_heading','YOUR TABLE. YOUR NIGHT.','below','VIP Tables — heading','text',36),
('below_vip_p1','For groups wanting their own space, Below offers a limited number of VIP tables with dedicated service and an elevated bottle-service experience.','below','VIP Tables — paragraph 1','textarea',37),
('below_vip_p2','Birthdays. Celebrations. Big nights. Or no reason at all.','below','VIP Tables — paragraph 2','textarea',38),
('below_vip_btn_label','VIP ENQUIRY','below','VIP Tables — button label','text',39),
('below_vip_btn_url','#','below','VIP Tables — button URL','text',40),
('below_vip_image','assets/img/below.jpg','below','VIP Tables — image','image',41),

('below_hours_heading','Below Hours','below','Hours — heading','text',42),
('below_hours_days','Wednesday - Sunday','below','Hours — days line','text',43),
('below_hours_cocktail_label','Cocktail Lounge','below','Hours — cocktail label (bold)','text',44),
('below_hours_cocktail','From 5:00pm','below','Hours — cocktail detail','text',45),
('below_hours_dj_label','DJs','below','Hours — DJs label (bold)','text',46),
('below_hours_dj','From 8:30pm','below','Hours — DJs detail','text',47),
('below_hours_note','Open Until Late. 18+, Valid Photo ID Required.','below','Hours — footnote','text',48);

-- Nights (Music section) — repeatable, editable via admin
INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`) VALUES
('below_night','Wednesday - RnB & Hip-Hop','90s and 2000s favourites meet the sounds of now.',1,1),
('below_night','Thursday - Afro & Melodic House','Rhythmic, hypnotic and built for late nights.',2,1),
('below_night','Friday - Vocal & Feel-Good House','Vocals, disco influence and house made for Friday night.',3,1),
('below_night','Saturday - Anthem House','Big melodies, elevated house and the signature Saturday sound of Below.',4,1),
('below_night','Sunday - Latin','Latin house, reggaeton and Afro-Latin sounds to close the week.',5,1);

-- DJ / line-up cards — placeholder content, replace via admin
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`link_url`,`image`,`sort`,`active`) VALUES
('below_dj','Lorem Ipsum Dolor','Sunday','4:00 PM to 8:00 PM','#','assets/img/below.jpg',1,1),
('below_dj','Lorem Ipsum Dolor','Sunday','4:00 PM to 8:00 PM','#','assets/img/below.jpg',2,1),
('below_dj','Lorem Ipsum Dolor','Sunday','4:00 PM to 8:00 PM','#','assets/img/below.jpg',3,1),
('below_dj','Lorem Ipsum Dolor','Sunday','4:00 PM to 8:00 PM','#','assets/img/below.jpg',4,1),
('below_dj','Lorem Ipsum Dolor','Wednesday','8:00 PM to 12:00 AM','#','assets/img/below.jpg',5,1),
('below_dj','Lorem Ipsum Dolor','Thursday','8:00 PM to 12:00 AM','#','assets/img/below.jpg',6,1),
('below_dj','Lorem Ipsum Dolor','Friday','9:00 PM to 1:00 AM','#','assets/img/below.jpg',7,1),
('below_dj','Lorem Ipsum Dolor','Saturday','9:00 PM to 1:00 AM','#','assets/img/below.jpg',8,1);

-- FAQs — placeholder content, replace via admin
INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`) VALUES
('below_faq','Lorem ipsum dolor sit amet consectetur?','Lorem ipsum dolor sit amet consectetur. Augue sed nisi non donec nam.',1,1),
('below_faq','Lorem ipsum dolor sit amet consectetur?','Lorem ipsum dolor sit amet consectetur. Augue sed nisi non donec nam.',2,1),
('below_faq','Lorem ipsum dolor sit?','Lorem fringilla pretium.',3,1),
('below_faq','Lorem ipsum dolor sit amet consectetur?','Lorem fringilla pretium.',4,1);

-- =====================================================================
-- What's On page
-- Note: the "See This Week's Line-Up" section on this page reuses the
-- SAME below_lineup_* settings and below_dj blocks as the Below page —
-- it is one shared section, not a separate copy.
-- =====================================================================
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('wo_hero_image','assets/img/cafe.jpg','whatson_page','Hero image (poster/fallback)','image',1),
('wo_hero_video','','whatson_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('wo_heading','What''s On','whatson_page','Heading','text',2),
('wo_subheading','Different Nights. Different Reasons To Come Back.','whatson_page','Subheading','text',3),
('wo_lede','Brunch. Happy Hour. Dinner parties. DJs. Guest artists. Late nights Below. See what''s happening across the House this week.','whatson_page','Lede paragraph (italic)','textarea',4),
('wo_tag1_label','CAFE','whatson_page','Tag button 1 label','text',5),
('wo_tag1_url','cafe.php','whatson_page','Tag button 1 URL','text',6),
('wo_tag2_label','RESTAURANT','whatson_page','Tag button 2 label','text',7),
('wo_tag2_url','restaurant.php','whatson_page','Tag button 2 URL','text',8),
('wo_tag3_label','BELOW','whatson_page','Tag button 3 label','text',9),
('wo_tag3_url','#lineup-section','whatson_page','Tag button 3 URL (use #lineup-section to scroll to the Line-Up section on this page)','text',10),

('wo_happyhour_eyebrow','Happy Hour','whatson_page','Happy Hour — eyebrow label','text',11),
('wo_happyhour_heading','INFORMATION','whatson_page','Happy Hour — heading','text',12),
('wo_happyhour_p1','Cocktails, wine and something from the kitchen as the House moves into the evening.','whatson_page','Happy Hour — paragraph 1','textarea',13),
('wo_happyhour_p2','A relaxed hour to ease from afternoon into night, before the room picks up.','whatson_page','Happy Hour — paragraph 2','textarea',14),
('wo_happyhour_btn_label','DISCOVER','whatson_page','Happy Hour — button label','text',15),
('wo_happyhour_btn_url','#','whatson_page','Happy Hour — button URL','text',16),
('wo_happyhour_image','assets/img/below.jpg','whatson_page','Happy Hour — image','image',17),

('wo_friday_eyebrow','Friday & Saturday Dinner Party','whatson_page','Fri/Sat — eyebrow label','text',18),
('wo_friday_heading','INFORMATION','whatson_page','Fri/Sat — heading','text',19),
('wo_friday_p1','From 6pm, dinner moves into another gear as the music, drinks and energy build around the room.','whatson_page','Fri/Sat — paragraph 1','textarea',20),
('wo_friday_p2','Expect a fuller room, a livelier soundtrack and a night that runs later than usual.','whatson_page','Fri/Sat — paragraph 2','textarea',21),
('wo_friday_btn_label','BOOK A TABLE','whatson_page','Fri/Sat — button label','text',22),
('wo_friday_btn_url','#','whatson_page','Fri/Sat — button URL','text',23),
('wo_friday_image','assets/img/below.jpg','whatson_page','Fri/Sat — image','image',24),

('wo_brunch_eyebrow','Weekend Bottomless Brunch','whatson_page','Brunch — eyebrow label','text',25),
('wo_brunch_heading','INFORMATION','whatson_page','Brunch — heading','text',26),
('wo_brunch_hours','**Saturday & Sunday:** 11:00 AM – 3:00 PM','whatson_page','Brunch — hours line (use **word** for bold)','text',27),
('wo_brunch_p1','Food, flowing drinks and weekend energy around the table.','whatson_page','Brunch — paragraph','textarea',28),
('wo_brunch_btn_label','BOOK BRUNCH','whatson_page','Brunch — button label','text',29),
('wo_brunch_btn_url','#','whatson_page','Brunch — button URL','text',30),
('wo_brunch_image','assets/img/below.jpg','whatson_page','Brunch — image','image',31);

-- FAQs — placeholder content, replace via admin
INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`) VALUES
('whatson_faq','Lorem ipsum dolor sit amet consectetur?','Lorem fringilla pretium.',1,1),
('whatson_faq','Lorem ipsum dolor sit amet consectetur?','Augue sed nec non donec nam.',2,1),
('whatson_faq','Lorem ipsum dolor sit.','Lorem fringilla pretium.',3,1),
('whatson_faq','Lorem ipsum dolor sit amet consectetur.','Lorem fringilla pretium.',4,1);

-- =====================================================================
-- Brunch page
-- Own menu dataset (brunch_menu_*) — independent from the Restaurant
-- page's menu (menu_*), even though it shows the same categories.
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
('brunch_guest_image','assets/img/below.jpg','brunch_page','Guest Artists — image','image',20);

-- Brunch gallery (placeholder photos — replace via admin)
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`) VALUES
('brunch_gallery','','assets/img/cafe.jpg',1,1),
('brunch_gallery','','assets/img/restaurant.jpg',2,1),
('brunch_gallery','','assets/img/below.jpg',3,1),
('brunch_gallery','','assets/img/hero.jpg',4,1),
('brunch_gallery','','assets/img/building.png',5,1);

-- Brunch menu categories (own dataset — admin can add/rename/remove — see admin/brunch.php)
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Brunch',1,1);
SET @brunch_cat_brunch := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Drink Menu',2,1);
SET @brunch_cat_dinner := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Desert',3,1);
SET @brunch_cat_dessert := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Cocktails',4,1);
SET @brunch_cat_cocktails := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Wine',5,1);
SET @brunch_cat_wine := LAST_INSERT_ID();

-- Brunch category menu items (other categories start empty — add via admin)
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`sort`,`active`) VALUES
(CONCAT('brunch_menu_item_', @brunch_cat_brunch),'Greek Omelette (GF)','22','Bell Pepper, Spinach, Tomato, PDO Feta Cheese',1,1),
(CONCAT('brunch_menu_item_', @brunch_cat_brunch),'Avocado Aegean Toast','21','Sourdough, Tomato, Lemon, Arugula, Basil, Shaved Parmesan, Poached Egg',2,1),
(CONCAT('brunch_menu_item_', @brunch_cat_brunch),'Smoked Salmon Eggs Benedict','26','Hollandaise Sauce, Spinach',3,1),
(CONCAT('brunch_menu_item_', @brunch_cat_brunch),'Truffle Fries','18','Parmigiano Reggiano, Chive',4,1),
(CONCAT('brunch_menu_item_', @brunch_cat_brunch),'French Toast','24','Caramelised Sicilian Pistachio',5,1),
(CONCAT('brunch_menu_item_', @brunch_cat_brunch),'CASA NEOS Gourmet Burger','39','1/3 Beef Patty, Brioche Bun, Smoked Cheddar, Caramelised Onions, Harissa Aioli, French Fries',6,1),
(CONCAT('brunch_menu_item_', @brunch_cat_brunch),'Farie Island Salmon Burger','27','Brioche Bun, Baby Arugula, Sauce Tartare, French Fries',7,1),
(CONCAT('brunch_menu_item_', @brunch_cat_brunch),'7oz Grilled Hanger Steak','36','Charred Lemon, Green Harissa',8,1);

-- =====================================================================
-- Dinner Party page
-- Own menu dataset (dp_menu_*) — independent from the Restaurant and
-- Brunch pages' menus, even though it reuses the same tabbed component.
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
('dp_guest_image','assets/img/below.jpg','dinnerparty_page','Guest Artists — image','image',20);

-- Gallery (placeholder photos — replace via admin)
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`) VALUES
('dp_gallery','','assets/img/below.jpg',1,1),
('dp_gallery','','assets/img/restaurant.jpg',2,1),
('dp_gallery','','assets/img/cafe.jpg',3,1),
('dp_gallery','','assets/img/hero.jpg',4,1),
('dp_gallery','','assets/img/building.png',5,1);

-- Dinner Party menu categories (own dataset — admin can add/rename/remove — see admin/dinnerparty.php)
-- Menu items intentionally left empty for all categories — add via admin.
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES
('dp_menu_cat','Dinner',1,1),
('dp_menu_cat','Drink Menu',2,1),
('dp_menu_cat','Desert',3,1),
('dp_menu_cat','Cocktails',4,1),
('dp_menu_cat','Wine',5,1);

-- =====================================================================
-- Private Events page
-- =====================================================================
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('pe_hero_image','assets/img/below.jpg','privateevents_page','Hero image (poster/fallback)','image',1),
('pe_hero_video','','privateevents_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('pe_heading','Private Events','privateevents_page','Heading','text',2),
('pe_subheading','Your Occasion. Your Space. Your Mood.','privateevents_page','Subheading','text',3),
('pe_lede','Bring your next celebration to House of Bungalow, where food, design, music and atmosphere come together under one Surry Hills address.','privateevents_page','Lede paragraph (italic)','textarea',4),
('pe_p1','From intimate dinners and long lunches to birthdays, cocktail receptions, corporate events and brand launches, the House offers different spaces for different occasions.','privateevents_page','Paragraph 1','textarea',5),
('pe_p2','Take over the Restaurant, go late in Below, or talk to us about creating something across the House.','privateevents_page','Paragraph 2','textarea',6),
('pe_p3','Our team will shape the food, drinks, music and details around the way you want the occasion to feel. Make yourself at home. We''ll take care of the rest.','privateevents_page','Paragraph 3','textarea',7),
('pe_btn_label','ENQUIRE ABOUT PRIVATE EVENTS','privateevents_page','Button label','text',8),
('pe_btn_url','#','privateevents_page','Button URL','text',9),
('pe_final_heading','Make The House Yours','privateevents_page','Closing — heading','text',10),
('pe_final_p1','For larger occasions, talk to our team about experiences across multiple spaces or selected exclusive-use options.','privateevents_page','Closing — paragraph','textarea',11),
('pe_final_btn_label','START PLANNING','privateevents_page','Closing — button label','text',12),
('pe_final_btn_url','#','privateevents_page','Closing — button URL','text',13);

-- Event cards (Restaurant / Below / Café / Full House Events)
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`link_url`,`image`,`sort`,`active`) VALUES
('pe_event_card','Restaurant Events','Long lunches. Private dinners. Cocktail receptions. Celebrations around the table.','The Restaurant offers an elevated but relaxed setting built around food, drinks and a room that comes alive around the people in it.','#','assets/img/restaurant.jpg',1,1),
('pe_event_card','Below Events','For something later, louder and deliberately different.','Below offers an intimate Basement setting for private celebrations, corporate events, brand activations and late-night experiences built around cocktails, music and atmosphere.','#','assets/img/below.jpg',2,1);

-- =====================================================================
-- Café page
-- =====================================================================
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('cafe_hero_image','assets/img/cafe.jpg','cafe_page','Hero image (poster/fallback)','image',1),
('cafe_hero_video','','cafe_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('cafe_heading','Café','cafe_page','Heading','text',2),
('cafe_subheading','Your Surry Hills Daily Ritual.','cafe_page','Subheading','text',3),
('cafe_lede','Coffee, food and mornings on Foveaux Street.','cafe_page','Lede (italic)','text',4),
('cafe_p1','House of Bungalow Café is our street-level neighbourhood café in the heart of Surry Hills — somewhere to stop for your morning coffee, grab something on the way to work, meet between meetings or stay a little longer.','cafe_page','Paragraph 1','textarea',5),
('cafe_p2','Serving coffee by The Grounds Roastery alongside pastries, sandwiches and café favourites.','cafe_page','Paragraph 2','textarea',6),
('cafe_gallery_image1','assets/img/cafe.jpg','cafe_page','Gallery photo 1','image',7),
('cafe_gallery_image2','assets/img/restaurant.jpg','cafe_page','Gallery photo 2','image',8),
('cafe_gallery_image3','assets/img/building.png','cafe_page','Gallery photo 3','image',9),
('cafe_hours_heading','Coffee. Food. Foveaux Street.','cafe_page','Hours — heading','text',10),
('cafe_hours_p1','Fast when you need it to be. Relaxed when you don''t.','cafe_page','Hours — paragraph 1','textarea',11),
('cafe_hours_p2','Whether it''s your first visit or your everyday order, the Café is designed to become part of your Surry Hills routine.','cafe_page','Hours — paragraph 2','textarea',12),
('cafe_hours','**Monday – Saturday:** 7:00 AM–3:00 PM','cafe_page','Hours line (use **word** for bold)','text',13),
('cafe_address','65 - 67 Foveaux Street, Surry Hills','cafe_page','Address line','text',14),
('cafe_btn1_label','VIEW MENU','cafe_page','Button 1 label','text',15),
('cafe_btn1_url','#','cafe_page','Button 1 URL','text',16),
('cafe_btn2_label','GET DIRECTIONS','cafe_page','Button 2 label','text',17),
('cafe_btn2_url','#','cafe_page','Button 2 URL','text',18),
('cafe_final_heading','More Of The House','cafe_page','Closing — heading','text',19),
('cafe_final_p1','The Café Is One Side Of House Of Bungalow.','cafe_page','Closing — paragraph 1','textarea',20),
('cafe_final_p2','Return For Lunch Or Dinner In The Restaurant, Or Head Below Foveaux Street After Dark.','cafe_page','Closing — paragraph 2','textarea',21),
('cafe_final_btn1_label','THE RESTAURANT','cafe_page','Closing — button 1 label','text',22),
('cafe_final_btn1_url','restaurant.php','cafe_page','Closing — button 1 URL','text',23),
('cafe_final_btn2_label','BELOW','cafe_page','Closing — button 2 label','text',24),
('cafe_final_btn2_url','below.php','cafe_page','Closing — button 2 URL','text',25);

-- =====================================================================
-- Popup Booking
-- Full-screen popup shown whenever a visitor clicks any button labelled
-- exactly "BOOK A TABLE" anywhere on the site (matched by text, not by
-- page — no per-page wiring needed).
-- =====================================================================
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('popup_heading','One Address. Every Mood','popup_booking','Heading','text',1),
('popup_subheading','Book Your Table Today','popup_booking','Subheading','text',2),
('popup_btn1_label','Below','popup_booking','Button 1 label','text',3),
('popup_btn1_url','below.php','popup_booking','Button 1 URL','text',4),
('popup_btn2_label','Restaurant','popup_booking','Button 2 label','text',5),
('popup_btn2_url','restaurant.php','popup_booking','Button 2 URL','text',6);

