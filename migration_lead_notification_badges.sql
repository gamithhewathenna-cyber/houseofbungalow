-- =====================================================================
-- Adds notification badges to the sidebar under "Lead Management" -
-- shows how many new, not-yet-viewed enquiries/applications/subscribers
-- are waiting, e.g. "Enquiries (3)". Opening a tab marks everything on
-- it as seen and clears its badge.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Not safe to run twice as-is (the ALTER TABLE lines error if the
-- column already exists) - if you've already run this once, skip it.
-- =====================================================================

ALTER TABLE `enquiries` ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `vip_bottle_enquiries` ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `career_applications` ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `subscribers` ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0;

-- Everything that already exists is marked as already-seen, so you don't
-- get a huge badge count for old entries the first time this runs.
UPDATE `enquiries` SET `is_read` = 1;
UPDATE `vip_bottle_enquiries` SET `is_read` = 1;
UPDATE `career_applications` SET `is_read` = 1;
UPDATE `subscribers` SET `is_read` = 1;
