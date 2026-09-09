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
('rest_hero_image','assets/img/restaurant.jpg','restaurant','Hero image','image',1),
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
('rest_hours_footnote','Walk-Ins Welcome. Book Ahead For Peak Hours Before Committing.','restaurant','Hours — footnote','text',49);

-- Brunch menu items (other categories start empty — add via admin)
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`sort`,`active`) VALUES
('menu_brunch','Greek Omelette (GF)','22','Bell Pepper, Spinach, Tomato, PDO Feta Cheese',1,1),
('menu_brunch','Avocado Aegean Toast','21','Sourdough, Tomato, Lemon, Arugula, Basil, Shaved Parmesan, Poached Egg',2,1),
('menu_brunch','Smoked Salmon Eggs Benedict','26','Hollandaise Sauce, Spinach',3,1),
('menu_brunch','Truffle Fries','18','Parmigiano Reggiano, Chive',4,1),
('menu_brunch','French Toast','24','Caramelised Sicilian Pistachio',5,1),
('menu_brunch','CASA HEOS Gourmet Burger','39','1/3 Beef Patty, Brioche Bun, Smoked Cheddar, Caramelised Onions, Harissa Aioli, French Fries',6,1),
('menu_brunch','Farro Island Salmon Burger','27','Brioche Bun, Baby Arugula, Sauce Tartare, French Fries',7,1),
('menu_brunch','7oz Grilled Hanger Steak','36','Charred Lemon, Green Harissa',8,1);

-- Restaurant gallery (placeholder photos — replace via admin)
INSERT INTO `blocks` (`block_type`,`title`,`image`,`sort`,`active`) VALUES
('rest_gallery','','assets/img/cafe.jpg',1,1),
('rest_gallery','','assets/img/restaurant.jpg',2,1),
('rest_gallery','','assets/img/below.jpg',3,1),
('rest_gallery','','assets/img/hero.jpg',4,1),
('rest_gallery','','assets/img/building.png',5,1);

