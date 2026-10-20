-- Replaces the Below page's placeholder "Lorem ipsum" FAQs with the real
-- content. Safe to run more than once: it only removes rows that still
-- have the original placeholder text (so it won't touch FAQs you've
-- already customised), and only seeds the real ones if the Below page
-- doesn't already have FAQs.
DELETE FROM `blocks` WHERE `block_type` = 'below_faq' AND `title` = 'Lorem ipsum dolor sit amet consectetur?';
DELETE FROM `blocks` WHERE `block_type` = 'below_faq' AND `title` = 'Lorem ipsum dolor sit?';

INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`)
SELECT * FROM (
  SELECT 'below_faq' AS block_type, 'What is Below?' AS title, 'Below is House of Bungalow''s late-night club lounge - a space for cocktails, Champagne, VIP bottle service and DJs, with the energy building as the night moves on.' AS body, 1 AS sort, 1 AS active
  UNION ALL SELECT 'below_faq','Do I need to book?','Walk-ins are welcome subject to capacity. VIP tables and bottle service should be booked in advance, particularly on Friday and Saturday nights.',2,1
  UNION ALL SELECT 'below_faq','Is Below 18+?','Yes. Below is strictly 18+ and valid photo identification may be required on entry.',3,1
  UNION ALL SELECT 'below_faq','Is there a dress code?','Yes. Below operates a strict evening dress and presentation standard. We ask all guests to arrive dressed for a late-night club lounge environment.\n\nEntry is not permitted in shorts, T-shirts, singlets, sportswear, activewear, tracksuits, jerseys, caps or hats. Guests with visible face or neck tattoos will also not be permitted entry. Dressing an item up with designer branding does not necessarily make it suitable for the venue.\n\nWe recommend smart, polished evening attire - shirts, tailored trousers, dresses, skirts, elevated separates and appropriate evening footwear.\n\nOur dress and entry standards apply to all guests, including ticket holders, guest-list guests, restaurant guests and VIP table bookings. A booking, ticket or guest-list confirmation does not guarantee entry if the venue''s entry requirements are not met.\n\nFinal entry remains at management''s discretion and is subject to responsible service of alcohol, behaviour, capacity and applicable licensing requirements.',4,1
  UNION ALL SELECT 'below_faq','What music does Below play?','Below has different sounds across the week, with DJs and programming changing by night. Check What''s On for the latest lineup and events.',5,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'below_faq');
