-- =====================================================================
-- House of Bungalow — What's On page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new What's On page content.
-- Note: the "See This Week's Line-Up" section on this page reuses the
-- SAME below_lineup_* settings and below_dj blocks as the Below page —
-- no new rows are needed for that part, it's shared automatically.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op
-- for settings; FAQs are only inserted if none exist yet).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('wo_hero_image','assets/img/cafe.jpg','whatson_page','Hero image (poster/fallback)','image',1),
('wo_hero_video','','whatson_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('wo_heading','What''s On','whatson_page','Heading','text',2),
('wo_subheading','Different Nights. Different Reasons To Come Back.','whatson_page','Subheading','text',3),
('wo_lede','Brunch. Happy Hour. Dinner parties. DJs. Guest artists. Late nights Below. See what''s happening across the House this week.','whatson_page','Lede paragraph (italic)','textarea',4),
('wo_tag1_label','CAFE','whatson_page','Tag button 1 label','text',5),
('wo_tag1_url','#','whatson_page','Tag button 1 URL','text',6),
('wo_tag2_label','RESTAURANT','whatson_page','Tag button 2 label','text',7),
('wo_tag2_url','restaurant.php','whatson_page','Tag button 2 URL','text',8),
('wo_tag3_label','BELOW','whatson_page','Tag button 3 label','text',9),
('wo_tag3_url','below.php','whatson_page','Tag button 3 URL','text',10),
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
('wo_brunch_image','assets/img/below.jpg','whatson_page','Brunch — image','image',31)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- FAQs — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`)
SELECT * FROM (
  SELECT 'whatson_faq' AS block_type, 'Lorem ipsum dolor sit amet consectetur?' AS title, 'Lorem fringilla pretium.' AS body, 1 AS sort, 1 AS active
  UNION ALL SELECT 'whatson_faq','Lorem ipsum dolor sit amet consectetur?','Augue sed nec non donec nam.',2,1
  UNION ALL SELECT 'whatson_faq','Lorem ipsum dolor sit.','Lorem fringilla pretium.',3,1
  UNION ALL SELECT 'whatson_faq','Lorem ipsum dolor sit amet consectetur.','Lorem fringilla pretium.',4,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'whatson_faq');
