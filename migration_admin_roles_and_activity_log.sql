-- =====================================================================
-- Adds admin user roles (Admin / Content Editor / Enquiries) and an
-- automatic "work history" activity log to the admin panel.
--
--   admin     - full access to everything, including Admin Users and
--               Website Settings
--   content   - can edit page content across the site only (every page
--               under Home Page, Reserve, Restaurant, Café, Below,
--               What's On, Brunch, Dinner Party, Melbourne Cup, Private
--               Events, Week's Line-Up, Popup Booking, FAQ, Contact,
--               Join Our Team, Privacy & Terms)
--   enquiries - can view/manage Enquiries, VIP Enquiries, Applications
--               and Subscribers only
--
-- Every admin account that already exists keeps full "admin" access
-- (the new column defaults to 'admin'), so nothing changes for your
-- current login until you assign a different role from Admin Users.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- The ALTER TABLE line is not safe to run twice (it errors if the `role`
-- column already exists) - if you've already run this once, delete that
-- one line before running it again. The CREATE TABLE below is always
-- safe to re-run.
-- =====================================================================

ALTER TABLE `admins` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'admin' AFTER `password_hash`;

CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) NULL,
  `username` VARCHAR(60) NOT NULL,
  `page` VARCHAR(80) NOT NULL,
  `action` VARCHAR(80) NOT NULL,
  `details` VARCHAR(255) NOT NULL DEFAULT '',
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
