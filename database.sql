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

