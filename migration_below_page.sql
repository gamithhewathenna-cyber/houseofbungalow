-- =====================================================================
-- House of Bungalow — Below page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new Below page content.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op
-- for settings; nights / DJ cards / FAQs are only inserted if none
-- exist yet, so re-running won't duplicate them).
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
('below_cocktails_p2','From 8:30 PM, the DJ takes over.','below','Cocktails — paragraph 2','textarea',13),
('below_cocktails_p3','The lights shift, the music builds and the room changes with it.','below','Cocktails — paragraph 3','textarea',14),
('below_cocktails_hours','Wednesday - Sunday: From 5 PM until late.','below','Cocktails — hours line','text',15),
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
('below_hours_note','Open Until Late. 18+, Valid Photo ID Required.','below','Hours — footnote','text',48)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Nights (Music section) — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`)
SELECT * FROM (SELECT
    'below_night' AS block_type, 'Wednesday - RnB & Hip-Hop' AS title, '90s and 2000s favourites meet the sounds of now.' AS body, 1 AS sort, 1 AS active
  UNION ALL SELECT 'below_night','Thursday - Afro & Melodic House','Rhythmic, hypnotic and built for late nights.',2,1
  UNION ALL SELECT 'below_night','Friday - Vocal & Feel-Good House','Vocals, disco influence and house made for Friday night.',3,1
  UNION ALL SELECT 'below_night','Saturday - Anthem House','Big melodies, elevated house and the signature Saturday sound of Below.',4,1
  UNION ALL SELECT 'below_night','Sunday - Latin','Latin house, reggaeton and Afro-Latin sounds to close the week.',5,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'below_night');

-- DJ / line-up cards — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`link_url`,`image`,`sort`,`active`)
SELECT * FROM (SELECT
    'below_dj' AS block_type, 'Lorem Ipsum Dolor' AS title, 'Sunday' AS subtitle, '4:00 PM to 8:00 PM' AS body, '#' AS link_url, 'assets/img/below.jpg' AS image, 1 AS sort, 1 AS active
  UNION ALL SELECT 'below_dj','Lorem Ipsum Dolor','Sunday','4:00 PM to 8:00 PM','#','assets/img/below.jpg',2,1
  UNION ALL SELECT 'below_dj','Lorem Ipsum Dolor','Sunday','4:00 PM to 8:00 PM','#','assets/img/below.jpg',3,1
  UNION ALL SELECT 'below_dj','Lorem Ipsum Dolor','Sunday','4:00 PM to 8:00 PM','#','assets/img/below.jpg',4,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'below_dj');

-- FAQs — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`)
SELECT * FROM (SELECT
    'below_faq' AS block_type, 'Lorem ipsum dolor sit amet consectetur?' AS title, 'Lorem ipsum dolor sit amet consectetur. Augue sed nisi non donec nam.' AS body, 1 AS sort, 1 AS active
  UNION ALL SELECT 'below_faq','Lorem ipsum dolor sit amet consectetur?','Lorem ipsum dolor sit amet consectetur. Augue sed nisi non donec nam.',2,1
  UNION ALL SELECT 'below_faq','Lorem ipsum dolor sit?','Lorem fringilla pretium.',3,1
  UNION ALL SELECT 'below_faq','Lorem ipsum dolor sit amet consectetur?','Lorem fringilla pretium.',4,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'below_faq');
