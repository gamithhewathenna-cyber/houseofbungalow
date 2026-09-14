-- =====================================================================
-- House of Bungalow — FAQ page migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the new FAQ page content.
-- Safe to run more than once (uses ON DUPLICATE KEY UPDATE as a no-op
-- for settings; the FAQ list is only inserted if none exist yet).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('faq_hero_image','assets/img/cafe.jpg','faq_page','Hero image (poster/fallback)','image',1),
('faq_hero_video','','faq_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('faq_heading','Frequently Asked Questions','faq_page','Heading','text',2),
('faq_subheading','House Of Bungalow','faq_page','Subheading','text',3)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- FAQ questions & answers — only seeded if none exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`)
SELECT * FROM (
  SELECT 'faq_page_item' AS block_type, 'What is House of Bungalow?' AS title, 'House of Bungalow is a multi-space venue in Surry Hills bringing together a Café, Restaurant and Below — a late-night basement bar — under one roof.' AS body, 1 AS sort, 1 AS active
  UNION ALL SELECT 'faq_page_item','Where is House of Bungalow?','We''re located at 65 - 67 Foveaux Street, Surry Hills NSW 2010, Sydney, Australia.',2,1
  UNION ALL SELECT 'faq_page_item','What food does House of Bungalow serve?','Our menu spans brunch, lunch and dinner, with share-style dishes and a seasonal a la carte menu across the Restaurant and Café.',3,1
  UNION ALL SELECT 'faq_page_item','Who is the chef at House of Bungalow?','Our kitchen is led by our Executive Chef — see The Chef section on the Restaurant page for more.',4,1
  UNION ALL SELECT 'faq_page_item','Does House of Bungalow take restaurant bookings?','Yes, bookings can be made online or by clicking Reserve at the top of any page.',5,1
  UNION ALL SELECT 'faq_page_item','Is House of Bungalow open for lunch?','Yes, the Restaurant is open for lunch — see our Restaurant Hours for current times.',6,1
  UNION ALL SELECT 'faq_page_item','Does House of Bungalow have brunch?','Yes, our Bottomless Brunch runs on weekends — see the Brunch page for the menu and hours.',7,1
  UNION ALL SELECT 'faq_page_item','What is Below at House of Bungalow?','Below is our intimate Basement bar built around DJs, cocktails and late nights.',8,1
  UNION ALL SELECT 'faq_page_item','Does Below have DJs?','Yes, Below hosts DJs and guest artists throughout the week — see This Week''s Line-Up for who''s playing.',9,1
  UNION ALL SELECT 'faq_page_item','Can I have dinner and then go to Below?','Absolutely — many guests start with dinner in the Restaurant and head down to Below afterwards.',10,1
  UNION ALL SELECT 'faq_page_item','Can I book a VIP table at Below?','Yes, a limited number of VIP tables are available at Below — enquire via the VIP Tables link on the Below page.',11,1
  UNION ALL SELECT 'faq_page_item','Is Below 18+?','Yes, Below is an 18+ venue.',12,1
  UNION ALL SELECT 'faq_page_item','Can I host a private event at House of Bungalow?','Yes — the Restaurant, Below or the whole House can be booked for private events. See our Private Events page to enquire.',13,1
  UNION ALL SELECT 'faq_page_item','What is House Collective?','House Collective is our group of venues and partners across Surry Hills — see the footer for more.',14,1
  UNION ALL SELECT 'faq_page_item','When does House of Bungalow open?','Opening hours vary by space — see the Hours section on the Restaurant, Café and Below pages.',15,1
  UNION ALL SELECT 'faq_page_item','What is the dress code?','We keep it smart-casual across the House — neat, comfortable and put-together.',16,1
  UNION ALL SELECT 'faq_page_item','Can House of Bungalow accommodate dietary requirements?','Yes, please let us know any dietary requirements when booking and our team will take care of the rest.',17,1
  UNION ALL SELECT 'faq_page_item','Is House of Bungalow accessible?','Yes, the venue is wheelchair accessible — contact us ahead of your visit if you have specific access needs.',18,1
  UNION ALL SELECT 'faq_page_item','Is there an entry fee for Below?','Entry is generally free, though select events or guest nights may carry a cover charge.',19,1
  UNION ALL SELECT 'faq_page_item','What is the cancellation or no-show policy?','We ask for as much notice as possible for cancellations — please contact us directly to change or cancel a booking.',20,1
  UNION ALL SELECT 'faq_page_item','What is the best way to get to House of Bungalow?','We''re a short walk from Central Station, with street parking and rideshare drop-off nearby on Foveaux Street.',21,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'faq_page_item');
