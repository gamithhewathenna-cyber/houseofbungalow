-- =====================================================================
-- Melbourne Cup page (melbournecup.php) - REPLACES the earlier
-- migration_melbournecup_page.sql. The page is no longer a Brunch-style
-- clone: it now has its own structure (dual Restaurant / Below Cup Day
-- offers, each with a fixed set menu, plus Beverage Packages for the
-- Restaurant and a Drinks list for Below, and a closing comparison
-- section). Fully separate admin at admin/melbournecup.php.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- If you already ran the earlier version of this migration, this one
-- first removes that placeholder content (old settings/gallery/menu
-- category) before seeding the real content - safe to run either way,
-- and safe to run more than once.
-- =====================================================================

-- Remove placeholder/superseded content from earlier versions of this migration (if present).
DELETE FROM `settings` WHERE `skey` IN (
  'mcup_lede1','mcup_lede2','mcup_hours','mcup_menu_heading','mcup_menu_lede',
  'mcup_guest_eyebrow','mcup_guest_heading','mcup_guest_p1','mcup_guest_p2',
  'mcup_guest_btn_label','mcup_guest_btn_url','mcup_guest_image',
  'mcup_rest_image','mcup_below_image'
);
DELETE FROM `blocks` WHERE `block_type` = 'mcup_gallery';
DELETE FROM `blocks` WHERE `block_type` IN (
  SELECT CONCAT('mcup_menu_item_', id) FROM (SELECT id FROM `blocks` WHERE `block_type` = 'mcup_menu_cat') AS t
);
DELETE FROM `blocks` WHERE `block_type` = 'mcup_menu_cat';

-- If an earlier run of this migration already inserted the old long-form headings
-- or combined price/package strings, shorten/restructure them to match the new
-- "Menu / Beverage Packages" browser layout.
UPDATE `settings` SET `svalue` = 'The Restaurant' WHERE `skey` = 'mcup_rest_heading' AND `svalue` = 'Melbourne Cup At The Restaurant';
UPDATE `settings` SET `svalue` = 'Below' WHERE `skey` = 'mcup_below_heading' AND `svalue` = 'Melbourne Cup At Below';
UPDATE `settings` SET `svalue` = '$120 PP' WHERE `skey` = 'mcup_rest_price' AND `svalue` = '$120 per person | Three-Course Shared Set Menu';
UPDATE `settings` SET `svalue` = '$160 PP' WHERE `skey` = 'mcup_below_price' AND `svalue` = '$160 per person | Two Hours of Canapés & Drinks';
UPDATE `settings` SET `svalue` = 'Standard' WHERE `skey` = 'mcup_rest_bev_standard_heading' AND `svalue` = 'Standard - Add $55pp';
UPDATE `settings` SET `svalue` = 'Premium' WHERE `skey` = 'mcup_rest_bev_premium_heading' AND `svalue` = 'Premium - Add $85pp';
UPDATE `settings` SET `svalue` = 'The Table' WHERE `skey` = 'mcup_menus_rest_label' AND `svalue` IN ('At The Restaurant', 'For The Table');
UPDATE `settings` SET `svalue` = 'The Menu' WHERE `skey` = 'mcup_menus_below_label' AND `svalue` = 'At Below';

-- If an earlier run of this migration already converted the beverage items to
-- the "Label | Value" row format, revert them back to a plain one-per-line list.
UPDATE `settings` SET `svalue` = 'Moët & Chandon Impérial Brut\nHouse white and red wine\nMimosas\nAsahi\nSoft drinks'
  WHERE `skey` = 'mcup_rest_bev_standard_items' AND `svalue` = 'Champagne | Moët & Chandon Impérial Brut\nWhite & Red Wine | House white and red wine\nCocktail | Mimosa\nBeer | Asahi\nSoft Drinks | Coke, Diet Coke, Sprite, Ginger Ale';
UPDATE `settings` SET `svalue` = 'Moët & Chandon Impérial Brut\nMaison Piron Beaujolais\nLeeuwin Estate Cabernet Sauvignon\nLe Fauve Rosé\nCloudy Bay Sauvignon Blanc\nScorpo Pinot Gris\nMimosas, Hugo Spritz and Negroni\nAsahi\nSomersby Apple Cider\nSoft drinks'
  WHERE `skey` = 'mcup_rest_bev_premium_items' AND `svalue` = 'Champagne | Moët & Chandon Impérial Brut\nWhite Wine | Cloudy Bay Sauvignon Blanc, Scorpo Pinot Gris\nRosé | Le Fauve Rosé\nRed Wine | Maison Piron Beaujolais, Leeuwin Estate Cabernet Sauvignon\nCocktails | Mimosa, Hugo Spritz, Negroni\nBeer & Cider | Asahi, Somersby Apple Cider\nSoft Drinks | Coke, Diet Coke, Sprite, Ginger Ale';

-- New settings (safe to re-run - existing rows are left untouched).
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('mcup_hero_image','assets/img/restaurant.jpg','melbournecup_page','Hero image (poster/fallback)','image',1),
('mcup_hero_video','','melbournecup_page','Hero video (mp4/webm, ideally 1280×720)','video',1),

('mcup_heading','Melbourne Cup At House Of Bungalow','melbournecup_page','Top Intro - heading','text',2),
('mcup_subheading','One Race. Two Ways To Celebrate.','melbournecup_page','Top Intro - subheading','text',3),
('mcup_lede','Make an afternoon of it.','melbournecup_page','Top Intro - lede','text',4),
('mcup_p1','This Melbourne Cup, House of Bungalow brings two distinct celebrations to Surry Hills.','melbournecup_page','Top Intro - paragraph 1','textarea',5),
('mcup_p2','Settle in at the Restaurant for a three-course shared dining experience, or head Below for an afternoon of champagne, cocktails and canapés.','melbournecup_page','Top Intro - paragraph 2','textarea',6),
('mcup_p3','Two spaces. Two different ways to spend Cup Day.','melbournecup_page','Top Intro - paragraph 3','textarea',7),
('mcup_date','Tuesday, 3 November 2026','melbournecup_page','Top Intro - date line','text',8),
('mcup_btn1_label','BOOK THE RESTAURANT','melbournecup_page','Top Intro - button 1 label','text',9),
('mcup_btn1_url','reserve.php','melbournecup_page','Top Intro - button 1 URL','text',10),
('mcup_btn2_label','BOOK BELOW','melbournecup_page','Top Intro - button 2 label','text',11),
('mcup_btn2_url','reserve.php','melbournecup_page','Top Intro - button 2 URL','text',12),

('mcup_rest_eyebrow','Melbourne Cup At','melbournecup_page','Restaurant - eyebrow label','text',13),
('mcup_rest_heading','The Restaurant','melbournecup_page','Restaurant - heading','text',13),
('mcup_rest_subheading','A Long Lunch Worth Dressing Up For.','melbournecup_page','Restaurant - subheading','text',14),
('mcup_rest_lede','Good food, flowing drinks and a table worth staying at.','melbournecup_page','Restaurant - lede','text',15),
('mcup_rest_p1','Celebrate Melbourne Cup at the Restaurant with a three-course menu designed for sharing, bringing together Spanish inspiration, Asian influences and the best of Australian produce.','melbournecup_page','Restaurant - paragraph 1','textarea',16),
('mcup_rest_p2','Begin with a welcome drink before settling in for an afternoon of generous food, good company and the excitement of Cup Day.','melbournecup_page','Restaurant - paragraph 2','textarea',17),
('mcup_rest_date','Tuesday, 3 November 2026','melbournecup_page','Restaurant - date line','text',18),
('mcup_rest_price_eyebrow','Three-Course Lunch','melbournecup_page','Restaurant - price eyebrow','text',19),
('mcup_rest_price','$120 PP','melbournecup_page','Restaurant - big price','text',19),
('mcup_rest_price_note','Includes a welcome drink.','melbournecup_page','Restaurant - price note','text',20),
('mcup_rest_btn_label','BOOK THE RESTAURANT','melbournecup_page','Restaurant - button label','text',21),
('mcup_rest_btn_url','reserve.php','melbournecup_page','Restaurant - button URL','text',22),

('mcup_rest_bev_lede','Make an afternoon of it.','melbournecup_page','Beverage Packages - lede','text',23),
('mcup_rest_bev_intro','Complete your Melbourne Cup experience with an optional three-hour beverage package.','melbournecup_page','Beverage Packages - intro','textarea',24),
('mcup_rest_bev_standard_heading','Standard','melbournecup_page','Beverage Packages - Standard label','text',25),
('mcup_rest_bev_standard_price','$55 PP','melbournecup_page','Beverage Packages - Standard price','text',25),
('mcup_rest_bev_standard_items','Moët & Chandon Impérial Brut\nHouse white and red wine\nMimosas\nAsahi\nSoft drinks','melbournecup_page','Beverage Packages - Standard items (one per line)','textarea',26),
('mcup_rest_bev_premium_heading','Premium','melbournecup_page','Beverage Packages - Premium label','text',27),
('mcup_rest_bev_premium_price','$85 PP','melbournecup_page','Beverage Packages - Premium price','text',27),
('mcup_rest_bev_premium_items','Moët & Chandon Impérial Brut\nMaison Piron Beaujolais\nLeeuwin Estate Cabernet Sauvignon\nLe Fauve Rosé\nCloudy Bay Sauvignon Blanc\nScorpo Pinot Gris\nMimosas, Hugo Spritz and Negroni\nAsahi\nSomersby Apple Cider\nSoft drinks','melbournecup_page','Beverage Packages - Premium items (one per line)','textarea',28),
('mcup_rest_bev_note1','All guests at the same table must select the same beverage package.','melbournecup_page','Beverage Packages - note (shown under the packages)','text',29),
('mcup_rest_bev_note2','Vegetarian and vegan options are available with advance notice. Please advise us of dietary requirements when booking.','melbournecup_page','Beverage Packages - dietary note','textarea',30),

('mcup_below_eyebrow','Melbourne Cup At','melbournecup_page','Below - eyebrow label','text',31),
('mcup_below_heading','Below','melbournecup_page','Below - heading','text',31),
('mcup_below_subheading','Champagne. Canapés. Cup Day Below.','melbournecup_page','Below - subheading','text',32),
('mcup_below_lede','A different way to spend the afternoon.','melbournecup_page','Below - lede','text',33),
('mcup_below_p1','This Melbourne Cup, Below opens its doors for an afternoon of champagne, cocktails and canapés in the House''s intimate Basement.','melbournecup_page','Below - paragraph 1','textarea',34),
('mcup_below_p2','Enjoy two hours of flowing drinks and a selection of canapés from the House of Bungalow kitchen, with the atmosphere and energy that make Below distinctly its own.','melbournecup_page','Below - paragraph 2','textarea',35),
('mcup_below_date','Tuesday, 3 November 2026','melbournecup_page','Below - date line','text',36),
('mcup_below_price_eyebrow','Two Hours Of Canapés & Drinks','melbournecup_page','Below - price eyebrow','text',37),
('mcup_below_price','$160 PP','melbournecup_page','Below - big price','text',37),
('mcup_below_btn_label','BOOK BELOW','melbournecup_page','Below - button label','text',38),
('mcup_below_btn_url','reserve.php','melbournecup_page','Below - button URL','text',39),

('mcup_below_drinks_lede','Champagne, cocktails and a considered selection of wines.','melbournecup_page','The Drinks - lede','text',40),
('mcup_below_drinks_champagne_items','Moët & Chandon Impérial Brut','melbournecup_page','The Drinks - Champagne (one per line)','textarea',41),
('mcup_below_drinks_white_items','Cloudy Bay Sauvignon Blanc\nScorpo Pinot Gris','melbournecup_page','The Drinks - White Wine (one per line)','textarea',42),
('mcup_below_drinks_rose_items','Provence Rosé\nCharteris Le Fauve Rosé','melbournecup_page','The Drinks - Rosé (one per line)','textarea',43),
('mcup_below_drinks_red_items','Otago Pinot Noir\nLeeuwin Estate Prelude Cabernet Sauvignon','melbournecup_page','The Drinks - Red Wine (one per line)','textarea',44),
('mcup_below_drinks_beer_items','Asahi','melbournecup_page','The Drinks - Beer (one per line)','textarea',45),
('mcup_below_drinks_cocktails_items','A selection of two signature cocktails','melbournecup_page','The Drinks - Signature Cocktails (one per line)','textarea',46),

('mcup_menus_heading','The Menu','melbournecup_page','The Menu section - heading','text',46),
('mcup_menus_rest_label','The Table','melbournecup_page','The Menu section - switcher label 1 (The Table)','text',46),
('mcup_menus_below_label','The Menu','melbournecup_page','The Menu section - switcher label 2 (The Menu)','text',46),

('mcup_summary_heading','Two Experiences. One House.','melbournecup_page','Closing Summary - heading','text',47),
('mcup_summary_subheading','Your Cup Day, Your Way.','melbournecup_page','Closing Summary - subheading','text',48),
('mcup_summary_p1','Whether it''s a long lunch at the Restaurant or champagne and canapés Below, make House of Bungalow your destination this Melbourne Cup.','melbournecup_page','Closing Summary - paragraph','textarea',49),
('mcup_summary_date','Tuesday, 3 November 2026','melbournecup_page','Closing Summary - date line','text',50),
('mcup_summary_rest_detail1','Three-Course Shared Set Menu · $120pp','melbournecup_page','Closing Summary - Restaurant detail 1','text',51),
('mcup_summary_rest_detail2','Optional Three-Hour Beverage Packages','melbournecup_page','Closing Summary - Restaurant detail 2','text',52),
('mcup_summary_rest_btn_label','BOOK THE RESTAURANT','melbournecup_page','Closing Summary - Restaurant button label','text',53),
('mcup_summary_rest_btn_url','reserve.php','melbournecup_page','Closing Summary - Restaurant button URL','text',54),
('mcup_summary_below_detail1','Two Hours of Canapés & Drinks · $160pp','melbournecup_page','Closing Summary - Below detail','text',55),
('mcup_summary_below_btn_label','BOOK BELOW','melbournecup_page','Closing Summary - Below button label','text',56),
('mcup_summary_below_btn_url','reserve.php','melbournecup_page','Closing Summary - Below button URL','text',57)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Restaurant Cup Day menu - only seeded if no mcup_rest_menu_cat rows exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`)
SELECT 'mcup_rest_menu_cat','For The Table',1,1 FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_rest_menu_cat' AND title = 'For The Table');
SET @mcup_rest_cat_table := (SELECT id FROM `blocks` WHERE block_type='mcup_rest_menu_cat' AND title='For The Table' LIMIT 1);

INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`)
SELECT 'mcup_rest_menu_cat','Entrée',2,1 FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_rest_menu_cat' AND title = 'Entrée');
SET @mcup_rest_cat_entree := (SELECT id FROM `blocks` WHERE block_type='mcup_rest_menu_cat' AND title='Entrée' LIMIT 1);

-- Rename an already-seeded "Main (Served As An Alternate Drop)" category (from an
-- earlier run) to the shorter "Main" + a separate italic note, matching the card layout.
UPDATE `blocks` SET `title` = 'Main', `subtitle` = 'Served as an alternate drop.'
WHERE `block_type` = 'mcup_rest_menu_cat' AND `title` = 'Main (Served As An Alternate Drop)';

INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`sort`,`active`)
SELECT 'mcup_rest_menu_cat','Main','Served as an alternate drop.',3,1 FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_rest_menu_cat' AND title IN ('Main','Main (Served As An Alternate Drop)'));
SET @mcup_rest_cat_main := (SELECT id FROM `blocks` WHERE block_type='mcup_rest_menu_cat' AND title='Main' LIMIT 1);

INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`)
SELECT * FROM (
  SELECT CONCAT('mcup_rest_menu_item_', @mcup_rest_cat_table) AS block_type,'Pan de cristal' AS title,'crushed vine tomato, EVOO' AS body,1 AS sort,1 AS active
  UNION ALL SELECT CONCAT('mcup_rest_menu_item_', @mcup_rest_cat_table),'Smoked eel gilda','Gordal olive, guindilla, kabayaki glaze',2,1
  UNION ALL SELECT CONCAT('mcup_rest_menu_item_', @mcup_rest_cat_entree),'Kingfish sashimi','chilled ajoblanco, rockfruit, green almonds, makrut kosho oil',1,1
  UNION ALL SELECT CONCAT('mcup_rest_menu_item_', @mcup_rest_cat_entree),'Hand chopped Wagyu','karashi sabayon, smoked soy, pickled chillies, potato crisp',2,1
  UNION ALL SELECT CONCAT('mcup_rest_menu_item_', @mcup_rest_cat_main),'Bundarra pork secreto','yuzu kosho mojo verde, butter beans, pickled turnips',1,1
  UNION ALL SELECT CONCAT('mcup_rest_menu_item_', @mcup_rest_cat_main),'Rangers Valley bavette MS5+','black garlic sofrito, piquillo ponzu',2,1
  UNION ALL SELECT CONCAT('mcup_rest_menu_item_', @mcup_rest_cat_main),'Crispy smashed potatoes','mixed bitter leaves, to share',3,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type LIKE 'mcup_rest_menu_item_%');

-- If the Restaurant Cup Day dishes above were already seeded by an earlier run
-- (with Title Case names), switch them to sentence case so each dish now reads
-- as one combined "Name, description" line on the live page.
UPDATE `blocks` SET `title` = 'Pan de cristal', `body` = 'crushed vine tomato, EVOO' WHERE `block_type` LIKE 'mcup_rest_menu_item_%' AND `title` = 'Pan De Cristal';
UPDATE `blocks` SET `title` = 'Smoked eel gilda' WHERE `block_type` LIKE 'mcup_%_menu_item_%' AND `title` = 'Smoked Eel Gilda';
UPDATE `blocks` SET `title` = 'Kingfish sashimi', `body` = 'chilled ajoblanco, rockfruit, green almonds, makrut kosho oil' WHERE `block_type` LIKE 'mcup_rest_menu_item_%' AND `title` = 'Kingfish Sashimi';
UPDATE `blocks` SET `title` = 'Hand chopped Wagyu', `body` = 'karashi sabayon, smoked soy, pickled chillies, potato crisp' WHERE `block_type` = CONCAT('mcup_rest_menu_item_', @mcup_rest_cat_entree) AND `title` = 'Hand Chopped Wagyu';
UPDATE `blocks` SET `title` = 'Bundarra pork secreto', `body` = 'yuzu kosho mojo verde, butter beans, pickled turnips' WHERE `block_type` LIKE 'mcup_rest_menu_item_%' AND `title` = 'Bundarra Pork Secreto';
UPDATE `blocks` SET `title` = 'Rangers Valley bavette MS5+', `body` = 'black garlic sofrito, piquillo ponzu' WHERE `block_type` LIKE 'mcup_rest_menu_item_%' AND `title` = 'Rangers Valley Bavette MS5+';
UPDATE `blocks` SET `title` = 'Crispy smashed potatoes', `body` = 'mixed bitter leaves, to share' WHERE `block_type` LIKE 'mcup_rest_menu_item_%' AND `title` = 'Crispy Smashed Potatoes';

-- Below Cup Day menu - only seeded if no mcup_below_menu_cat rows exist yet.
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`)
SELECT 'mcup_below_menu_cat','On Arrival',1,1 FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_below_menu_cat');
SET @mcup_below_cat_arrival := (SELECT id FROM `blocks` WHERE block_type='mcup_below_menu_cat' AND title='On Arrival' LIMIT 1);

INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`)
SELECT 'mcup_below_menu_cat','Cold',2,1 FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_below_menu_cat' AND title = 'Cold');
SET @mcup_below_cat_cold := (SELECT id FROM `blocks` WHERE block_type='mcup_below_menu_cat' AND title='Cold' LIMIT 1);

INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`)
SELECT 'mcup_below_menu_cat','Hot',3,1 FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_below_menu_cat' AND title = 'Hot');
SET @mcup_below_cat_hot := (SELECT id FROM `blocks` WHERE block_type='mcup_below_menu_cat' AND title='Hot' LIMIT 1);

INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`)
SELECT 'mcup_below_menu_cat','Substantial',4,1 FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_below_menu_cat' AND title = 'Substantial');
SET @mcup_below_cat_substantial := (SELECT id FROM `blocks` WHERE block_type='mcup_below_menu_cat' AND title='Substantial' LIMIT 1);

INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`)
SELECT 'mcup_below_menu_cat','Sweet',5,1 FROM (SELECT 1) AS d
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type = 'mcup_below_menu_cat' AND title = 'Sweet');
SET @mcup_below_cat_sweet := (SELECT id FROM `blocks` WHERE block_type='mcup_below_menu_cat' AND title='Sweet' LIMIT 1);

INSERT INTO `blocks` (`block_type`,`title`,`body`,`sort`,`active`)
SELECT * FROM (
  SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_arrival) AS block_type,'Rock oysters' AS title,'sudachi, cured Wagyu' AS body,1 AS sort,1 AS active
  UNION ALL SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_arrival),'Smoked eel gilda','Gordal olive, guindilla',2,1
  UNION ALL SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_cold),'Sea urchin pie tee','egg yolk emulsion, caramelised onions, chive flower',1,1
  UNION ALL SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_cold),'Hand chopped Wagyu','karashi sabayon, potato pave',2,1
  UNION ALL SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_hot),'Kimchi croqueta','bluefin tuna, jamón XO',1,1
  UNION ALL SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_hot),'House Won-Ton','spicy carbonara, Abrolhos Island scallops',2,1
  UNION ALL SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_hot),'King prawn spring roll','chorizo, burnt mandarin sweet and sour',3,1
  UNION ALL SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_substantial),'Dry-aged beef mini burger','mature cheddar, smoked Idiazabal, karashi sumiso mayo, PX caramelised onions, pickles',1,1
  UNION ALL SELECT CONCAT('mcup_below_menu_item_', @mcup_below_cat_sweet),'Rocher','foie, Jivara chocolate, PX drunken raisins, hazelnut',1,1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `blocks` WHERE block_type LIKE 'mcup_below_menu_item_%');

-- Same sentence-case fix for the Below Cup Day dishes, if already seeded.
UPDATE `blocks` SET `title` = 'Rock oysters', `body` = 'sudachi, cured Wagyu' WHERE `block_type` LIKE 'mcup_below_menu_item_%' AND `title` = 'Rock Oysters';
UPDATE `blocks` SET `title` = 'Sea urchin pie tee', `body` = 'egg yolk emulsion, caramelised onions, chive flower' WHERE `block_type` LIKE 'mcup_below_menu_item_%' AND `title` = 'Sea Urchin Pie Tee';
UPDATE `blocks` SET `title` = 'Hand chopped Wagyu', `body` = 'karashi sabayon, potato pave' WHERE `block_type` = CONCAT('mcup_below_menu_item_', @mcup_below_cat_cold) AND `title` = 'Hand Chopped Wagyu';
UPDATE `blocks` SET `title` = 'Kimchi croqueta', `body` = 'bluefin tuna, jamón XO' WHERE `block_type` LIKE 'mcup_below_menu_item_%' AND `title` = 'Kimchi Croqueta';
UPDATE `blocks` SET `body` = 'spicy carbonara, Abrolhos Island scallops' WHERE `block_type` LIKE 'mcup_below_menu_item_%' AND `title` = 'House Won-Ton';
UPDATE `blocks` SET `title` = 'King prawn spring roll', `body` = 'chorizo, burnt mandarin sweet and sour' WHERE `block_type` LIKE 'mcup_below_menu_item_%' AND `title` = 'King Prawn Spring Roll';
UPDATE `blocks` SET `title` = 'Dry-aged beef mini burger', `body` = 'mature cheddar, smoked Idiazabal, karashi sumiso mayo, PX caramelised onions, pickles' WHERE `block_type` LIKE 'mcup_below_menu_item_%' AND `title` = 'Dry-Aged Beef Mini Burger';
UPDATE `blocks` SET `body` = 'foie, Jivara chocolate, PX drunken raisins, hazelnut' WHERE `block_type` LIKE 'mcup_below_menu_item_%' AND `title` = 'Rocher';
