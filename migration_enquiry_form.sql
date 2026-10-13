-- Adds the "enquiries" table that stores submissions from the new
-- ENQUIRE popup form on the Private Events page (event cards section).
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `event_type` VARCHAR(80) NOT NULL DEFAULT '',
  `preferred_space` VARCHAR(80) NOT NULL DEFAULT '',
  `preferred_date` VARCHAR(40) NOT NULL DEFAULT '',
  `preferred_time` VARCHAR(40) NOT NULL DEFAULT '',
  `guests` VARCHAR(40) NOT NULL DEFAULT '',
  `message` TEXT NULL,
  `source` VARCHAR(160) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
