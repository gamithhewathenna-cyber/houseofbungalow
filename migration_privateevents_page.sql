-- =====================================================================
-- House of Bungalow — Private Events page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new Private Events page content.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op
-- for settings; the event cards are only inserted if none exist yet).
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
('pe_final_btn_url','#','privateevents_page','Closing — button URL','text',13)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Event cards — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`link_url`,`image`,`sort`,`active`)
SELECT * FROM (
  SELECT 'pe_event_card' AS block_type, 'Restaurant Events' AS title, 'Long lunches. Private dinners. Cocktail receptions. Celebrations around the table.' AS subtitle, 'The Restaurant offers an elevated but relaxed setting built around food, drinks and a room that comes alive around the people in it.' AS body, '#' AS link_url, 'assets/img/restaurant.jpg' AS image, 1 AS sort, 1 AS active
  UNION ALL SELECT 'pe_event_card','Below Events','For something later, louder and deliberately different.','Below offers an intimate Basement setting for private celebrations, corporate events, brand activations and late-night experiences built around cocktails, music and atmosphere.','#','assets/img/below.jpg',2,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'pe_event_card');
