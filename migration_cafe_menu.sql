-- =====================================================================
-- Café page — adds a full Menu section (Hot Drinks, Iced Drinks, Tea
-- Selection, Smoothies, Frappes, Sandwiches, Pastries), with the same
-- admin-manageable categories/items pattern used on the Restaurant,
-- Brunch and Dinner Party pages.
-- Run this once via cPanel > phpMyAdmin > SQL tab. Settings are safe to
-- re-run (ON DUPLICATE KEY UPDATE is a no-op); the categories/items
-- below should only be run once, since re-running would create a
-- second set of categories.
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('cafe_menu_heading','Café Menu','cafe_page','Menu - heading','text',7),
('cafe_menu_lede','','cafe_page','Menu - subheading','textarea',8),
('cafe_menu_footnote','A 1.4% surcharge applies to all card payments.','cafe_page','Menu - footnote','text',9)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('cafe_menu_cat','Hot Drinks',1,1);
SET @cafe_cat_hot := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('cafe_menu_cat','Iced Drinks',2,1);
SET @cafe_cat_iced := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('cafe_menu_cat','Tea Selection',3,1);
SET @cafe_cat_tea := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('cafe_menu_cat','Smoothies',4,1);
SET @cafe_cat_smoothies := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('cafe_menu_cat','Frappes',5,1);
SET @cafe_cat_frappes := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('cafe_menu_cat','Sandwiches',6,1);
SET @cafe_cat_sandwiches := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('cafe_menu_cat','Pastries',7,1);
SET @cafe_cat_pastries := LAST_INSERT_ID();

INSERT INTO `blocks` (`block_type`,`title`,`subtitle`,`body`,`sort`,`active`) VALUES
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Espresso / Macchiato','$4','',1,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Piccolo','$4.50','',2,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Cappuccino','$5 / $6','',3,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Flat White','$5 / $6','',4,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Latte','$5 / $6','',5,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Long Black','$5 / $6','',6,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Mocha','$5.50 / $6.50','',7,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Hot Chocolate','$5 / $6','',8,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Chai Latte','$6 / $7','',9,1),
(CONCAT('cafe_menu_item_', @cafe_cat_hot),'Matcha Latte','$6.50 / $7.50','',10,1),

(CONCAT('cafe_menu_item_', @cafe_cat_iced),'Cold Brew','$7.50','',1,1),
(CONCAT('cafe_menu_item_', @cafe_cat_iced),'Iced Latte','$7','',2,1),
(CONCAT('cafe_menu_item_', @cafe_cat_iced),'Iced Long Black','$7','',3,1),
(CONCAT('cafe_menu_item_', @cafe_cat_iced),'Iced Mocha','$7.50','',4,1),
(CONCAT('cafe_menu_item_', @cafe_cat_iced),'Iced Chocolate','$7.50','',5,1),
(CONCAT('cafe_menu_item_', @cafe_cat_iced),'Iced Chai','$7.50','',6,1),
(CONCAT('cafe_menu_item_', @cafe_cat_iced),'Iced Matcha','$8','',7,1),
(CONCAT('cafe_menu_item_', @cafe_cat_iced),'Iced Strawberry','$10','',8,1),

(CONCAT('cafe_menu_item_', @cafe_cat_tea),'English Breakfast','$5.50','',1,1),
(CONCAT('cafe_menu_item_', @cafe_cat_tea),'Earl Grey','$5.50','',2,1),
(CONCAT('cafe_menu_item_', @cafe_cat_tea),'Green Sencha','$5.50','',3,1),
(CONCAT('cafe_menu_item_', @cafe_cat_tea),'Peppermint','$5.50','',4,1),
(CONCAT('cafe_menu_item_', @cafe_cat_tea),'Lemongrass & Ginger','$5.50','',5,1),

(CONCAT('cafe_menu_item_', @cafe_cat_smoothies),'Banana','$10','Add Whey Protein +$3',1,1),
(CONCAT('cafe_menu_item_', @cafe_cat_smoothies),'Mango','$10','',2,1),
(CONCAT('cafe_menu_item_', @cafe_cat_smoothies),'Mixed Berries','$10','',3,1),

(CONCAT('cafe_menu_item_', @cafe_cat_frappes),'Açaí','$12','',1,1),
(CONCAT('cafe_menu_item_', @cafe_cat_frappes),'Tropical','$10','Pineapple, mango & passionfruit.',2,1),
(CONCAT('cafe_menu_item_', @cafe_cat_frappes),'Sunrise','$10','Strawberries, pineapple, mango & kiwi.',3,1),

(CONCAT('cafe_menu_item_', @cafe_cat_sandwiches),'Prosciutto','Full $18 / Half $10','Focaccia - 18-month prosciutto, buffalo mozzarella, pesto, dehydrated tomato, rocket, EVOO.',1,1),
(CONCAT('cafe_menu_item_', @cafe_cat_sandwiches),'Mortadella','$16','Ciabatta - mortadella, buffalo mozzarella, pitted olives, rocket, balsamic vinegar, pistachio.',2,1),
(CONCAT('cafe_menu_item_', @cafe_cat_sandwiches),'Salami','$14','Ciabatta - Calabrian salami, provolone, pickled onion, hot honey, Dijon mustard, rocket.',3,1),
(CONCAT('cafe_menu_item_', @cafe_cat_sandwiches),'Smoked Salmon','$17','Bagel - smoked salmon, cream cheese, red onion, capers, dill.',4,1),
(CONCAT('cafe_menu_item_', @cafe_cat_sandwiches),'Cauliflower (Vegan)','$12','Ciabatta - roasted cauliflower, hummus, pickled red onion, rocket, toasted almonds, lemon juice.',5,1),
(CONCAT('cafe_menu_item_', @cafe_cat_sandwiches),'Pumpkin & Feta (Vegetarian)','Full $13 / Half $8','Focaccia - roasted kabocha, Danish feta, toasted cashews, miso glaze, rocket, balsamic glaze.',6,1),

(CONCAT('cafe_menu_item_', @cafe_cat_pastries),'Croissant','$5','',1,1),
(CONCAT('cafe_menu_item_', @cafe_cat_pastries),'Almond Croissant','$8','',2,1),
(CONCAT('cafe_menu_item_', @cafe_cat_pastries),'Chocolate Croissant','$6','',3,1),
(CONCAT('cafe_menu_item_', @cafe_cat_pastries),'Ham & Cheese Croissant','$9','',4,1),
(CONCAT('cafe_menu_item_', @cafe_cat_pastries),'Muffin - Mixed Berries','$7','',5,1),
(CONCAT('cafe_menu_item_', @cafe_cat_pastries),'Muffin - Hazelnut & Chocolate','$7','',6,1),
(CONCAT('cafe_menu_item_', @cafe_cat_pastries),'Banana Bread','$5','',7,1);
