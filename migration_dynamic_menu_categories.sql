-- =====================================================================
-- House of Bungalow — Dynamic menu categories migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database.
--
-- What this does: the Restaurant, Brunch, and Dinner Party menus used
-- to have a fixed set of 5 tabs (Brunch/Drink Menu/Desert/Cocktails/
-- Wine) hardcoded in the site's code. This migration converts each
-- page's tabs into rows in the `blocks` table (block_type =
-- '..._menu_cat'), so you can now add, rename, reorder or delete menu
-- categories yourself from Admin > [page] > Menu > Menu Categories.
-- Your existing menu items are preserved and re-linked to the new
-- category rows automatically — nothing is deleted.
--
-- IMPORTANT: this migration is NOT safe to run twice — running it
-- again would create duplicate categories. Run it once only.
-- =====================================================================

-- ---- Restaurant ----
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Brunch',1,1);
SET @rest_cat_brunch := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Drink Menu',2,1);
SET @rest_cat_dinner := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Desert',3,1);
SET @rest_cat_dessert := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Cocktails',4,1);
SET @rest_cat_cocktails := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('rest_menu_cat','Wine',5,1);
SET @rest_cat_wine := LAST_INSERT_ID();

UPDATE `blocks` SET `block_type` = CONCAT('rest_menu_item_', @rest_cat_brunch)    WHERE `block_type` = 'menu_brunch';
UPDATE `blocks` SET `block_type` = CONCAT('rest_menu_item_', @rest_cat_dinner)    WHERE `block_type` = 'menu_dinner';
UPDATE `blocks` SET `block_type` = CONCAT('rest_menu_item_', @rest_cat_dessert)   WHERE `block_type` = 'menu_dessert';
UPDATE `blocks` SET `block_type` = CONCAT('rest_menu_item_', @rest_cat_cocktails) WHERE `block_type` = 'menu_cocktails';
UPDATE `blocks` SET `block_type` = CONCAT('rest_menu_item_', @rest_cat_wine)      WHERE `block_type` = 'menu_wine';

-- ---- Brunch ----
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Brunch',1,1);
SET @brunch_cat_brunch := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Drink Menu',2,1);
SET @brunch_cat_dinner := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Desert',3,1);
SET @brunch_cat_dessert := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Cocktails',4,1);
SET @brunch_cat_cocktails := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('brunch_menu_cat','Wine',5,1);
SET @brunch_cat_wine := LAST_INSERT_ID();

UPDATE `blocks` SET `block_type` = CONCAT('brunch_menu_item_', @brunch_cat_brunch)    WHERE `block_type` = 'brunch_menu_brunch';
UPDATE `blocks` SET `block_type` = CONCAT('brunch_menu_item_', @brunch_cat_dinner)    WHERE `block_type` = 'brunch_menu_dinner';
UPDATE `blocks` SET `block_type` = CONCAT('brunch_menu_item_', @brunch_cat_dessert)   WHERE `block_type` = 'brunch_menu_dessert';
UPDATE `blocks` SET `block_type` = CONCAT('brunch_menu_item_', @brunch_cat_cocktails) WHERE `block_type` = 'brunch_menu_cocktails';
UPDATE `blocks` SET `block_type` = CONCAT('brunch_menu_item_', @brunch_cat_wine)      WHERE `block_type` = 'brunch_menu_wine';

-- ---- Dinner Party ----
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('dp_menu_cat','Dinner',1,1);
SET @dp_cat_dinner := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('dp_menu_cat','Drink Menu',2,1);
SET @dp_cat_drinks := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('dp_menu_cat','Desert',3,1);
SET @dp_cat_dessert := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('dp_menu_cat','Cocktails',4,1);
SET @dp_cat_cocktails := LAST_INSERT_ID();
INSERT INTO `blocks` (`block_type`,`title`,`sort`,`active`) VALUES ('dp_menu_cat','Wine',5,1);
SET @dp_cat_wine := LAST_INSERT_ID();

UPDATE `blocks` SET `block_type` = CONCAT('dp_menu_item_', @dp_cat_dinner)    WHERE `block_type` = 'dp_menu_dinner';
UPDATE `blocks` SET `block_type` = CONCAT('dp_menu_item_', @dp_cat_drinks)    WHERE `block_type` = 'dp_menu_drinks';
UPDATE `blocks` SET `block_type` = CONCAT('dp_menu_item_', @dp_cat_dessert)   WHERE `block_type` = 'dp_menu_dessert';
UPDATE `blocks` SET `block_type` = CONCAT('dp_menu_item_', @dp_cat_cocktails) WHERE `block_type` = 'dp_menu_cocktails';
UPDATE `blocks` SET `block_type` = CONCAT('dp_menu_item_', @dp_cat_wine)      WHERE `block_type` = 'dp_menu_wine';
