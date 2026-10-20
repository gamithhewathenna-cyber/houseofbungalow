-- =====================================================================
-- House of Bungalow — Café page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new Café page content.
-- Also points the What's On page's "CAFE" tag button at this new page
-- (it previously linked to "#").
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op).
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
('cafe_final_btn2_url','below.php','cafe_page','Closing — button 2 URL','text',25)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Point the What's On page's CAFE tag button at the new page (only if it
-- was never customised away from the original "#" placeholder).
UPDATE `settings` SET `svalue` = 'cafe.php'
  WHERE `skey` = 'wo_tag1_url' AND `section` = 'whatson_page' AND `svalue` = '#';
