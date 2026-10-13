-- Links the footer menu items to their matching pages.
UPDATE `blocks` SET `link_url` = 'restaurant.php'    WHERE `block_type` = 'footer_nav' AND `title` = 'RESTAURANT';
UPDATE `blocks` SET `link_url` = 'below.php'         WHERE `block_type` = 'footer_nav' AND `title` = 'BELOW';
UPDATE `blocks` SET `link_url` = 'cafe.php'          WHERE `block_type` = 'footer_nav' AND `title` = 'CAFÉ';
UPDATE `blocks` SET `link_url` = 'whatson.php'       WHERE `block_type` = 'footer_nav' AND `title` = 'WHAT''S ON';
UPDATE `blocks` SET `link_url` = 'privateevents.php' WHERE `block_type` = 'footer_nav' AND `title` = 'PRIVATE EVENTS';
