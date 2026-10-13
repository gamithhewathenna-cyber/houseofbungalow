-- =====================================================================
-- New "Join Our Team" page (careers.php) with an on-page application
-- form (not a popup) that accepts a CV upload (PDF/DOC/DOCX). Also
-- points the footer's "JOIN OUR TEAM" link at it.
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE / CREATE TABLE IF
-- NOT EXISTS are no-ops).
-- =====================================================================

CREATE TABLE IF NOT EXISTS `career_applications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(160) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `area_of_interest` VARCHAR(80) NOT NULL DEFAULT '',
  `position` VARCHAR(160) NOT NULL DEFAULT '',
  `cv_path` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('career_heading','Join Our Team','career_page','Heading','text',1),
('career_lede','Great hospitality starts with the people behind it.','career_page','Lede paragraph (italic)','textarea',2),
('career_p1','We''re always looking for passionate, talented people who love what they do, care about the details and know how to make guests feel at home.','career_page','Paragraph 1','textarea',3),
('career_p2','Whether your passion is food, drinks, service or nightlife, there''s a place for you in the House.','career_page','Paragraph 2','textarea',4),
('career_p3','Come be part of it.','career_page','Paragraph 3','textarea',5),
('career_form_eyebrow','Join The House','career_page','Form - eyebrow label','text',6),
('career_form_intro','Interested in working with us? Tell us a little about yourself and we''ll be in touch if there''s an opportunity that fits.','career_page','Form - intro text','textarea',7),
('career_success_message','Thanks for your interest in House of Bungalow. We''ve received your application and will be in touch if a suitable opportunity comes up.','career_page','Form - thank-you message (shown after submitting)','textarea',8),
('career_to_email','','career_page','Send applications to (email address)','text',9),
('career_cc_email','','career_page','CC email (optional - comma-separate for more than one)','text',10)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

UPDATE `blocks` SET `link_url` = 'careers.php' WHERE `block_type` = 'footer_nav' AND `title` = 'JOIN OUR TEAM';
