-- Adds a FAQs section to the Restaurant page, seeded with the 5 provided
-- questions. Only runs if the Restaurant page has no FAQs yet, so it's
-- safe to run more than once.
INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`)
SELECT * FROM (
  SELECT 'rest_faq' AS block_type, 'Do I need a reservation?' AS title, 'Bookings are recommended, particularly for Friday and Saturday evenings and for larger groups. Walk-ins are welcome subject to availability.' AS body, 1 AS sort, 1 AS active
  UNION ALL SELECT 'rest_faq','What style of food does The Restaurant serve?','The Restaurant serves modern Spanish-inspired food with Asian influence, designed around sharing, alongside a considered wine and cocktail program.',2,1
  UNION ALL SELECT 'rest_faq','Do you cater for dietary requirements?','Yes. Please include any allergies or dietary requirements when booking so our team can advise on suitable dishes and make appropriate arrangements.',3,1
  UNION ALL SELECT 'rest_faq','Can I book for a birthday, celebration or larger group?','Absolutely. We welcome birthdays, group dinners, long lunches and celebrations, with private dining and exclusive-hire options also available.',4,1
  UNION ALL SELECT 'rest_faq','Can I continue the night at Below after dinner?','Yes. Guests are welcome to continue downstairs at Below after dinner, subject to capacity and Below''s entry requirements.',5,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'rest_faq');
