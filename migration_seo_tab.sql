-- =====================================================================
-- Adds a new "SEO" tab to the admin panel, for managing SEO Title, SEO
-- Keyphrase, Meta Description and URL Slug for every page individually.
-- Changes made there are picked up automatically by includes/header.php
-- (title tag, meta description, meta keywords, canonical link) and, for
-- a custom URL slug, by .htaccess + seo-router.php.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE below is a no-op
-- on repeat runs - existing rows, including any SEO text you've already
-- entered, are left untouched).
-- =====================================================================

CREATE TABLE IF NOT EXISTS `seo_meta` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `page_key` VARCHAR(40) NOT NULL,
  `page_file` VARCHAR(60) NOT NULL,
  `page_label` VARCHAR(80) NOT NULL,
  `seo_title` VARCHAR(160) NOT NULL DEFAULT '',
  `seo_keyphrase` VARCHAR(160) NOT NULL DEFAULT '',
  `seo_description` VARCHAR(320) NOT NULL DEFAULT '',
  `seo_slug` VARCHAR(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_key` (`page_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `seo_meta` (`page_key`, `page_file`, `page_label`) VALUES
('index',         'index.php',         'Home'),
('restaurant',     'restaurant.php',    'Restaurant & Menus'),
('below',          'below.php',         'Below'),
('whatson',        'whatson.php',       "What's On"),
('melbournecup',   'melbournecup.php',  'Melbourne Cup'),
('brunch',         'brunch.php',        'Bottomless Brunch'),
('dinnerparty',    'dinnerparty.php',   'Dinner Party'),
('privateevents',  'privateevents.php', 'Private Events'),
('cafe',           'cafe.php',          'Café'),
('reserve',        'reserve.php',       'Reserve'),
('careers',        'careers.php',       'Join Our Team'),
('contact',        'contact.php',       'Contact'),
('faq',            'faq.php',           'FAQ'),
('privacy',        'privacy.php',       'Privacy & Terms')
ON DUPLICATE KEY UPDATE `page_key` = `page_key`;
