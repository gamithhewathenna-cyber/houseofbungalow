-- =====================================================================
-- Adds the new "Press & Media" page (press.php), its Media Enquiry
-- Form, and a "Press Enquiries" tab under Lead Management in the admin
-- (manage content: admin/press.php - view submitted enquiries:
-- admin/press-enquiries.php).
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE / IF NOT EXISTS
-- below are no-ops on repeat runs - existing values are left untouched).
-- =====================================================================

CREATE TABLE IF NOT EXISTS `press_enquiries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(160) NOT NULL,
  `organisation` VARCHAR(160) NOT NULL DEFAULT '',
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NOT NULL DEFAULT '',
  `enquiry_type` VARCHAR(80) NOT NULL DEFAULT '',
  `message` TEXT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('press_heading','Press & Media','press_page','Heading','text',1),
('press_subheading','STORIES FROM THE HOUSE.','press_page','Subheading','text',2),
('press_lede','Every space has a story. Here''s ours.','press_page','Lede paragraph (italic)','textarea',3),
('press_p1','House of Bungalow brings together food, design, music and hospitality across three distinct spaces in the heart of Surry Hills.','press_page','Paragraph 1','textarea',4),
('press_p2','From the people behind the House to the experiences within it, there''s always a story worth sharing.','press_page','Paragraph 2','textarea',5),
('press_p3','For press enquiries, interviews, features and media collaborations, get in touch with our team.','press_page','Paragraph 3','textarea',6),
('press_btn_label','MEDIA ENQUIRIES','press_page','Button label (scrolls down to the form)','text',7),
('press_form_eyebrow','Media Enquiry Form','press_page','Form - eyebrow label','text',8),
('press_form_intro','','press_page','Form - intro text (optional)','textarea',9),
('press_success_message','Thank you for getting in touch. Our team will be in contact shortly.','press_page','Form - thank-you message (shown after submitting)','textarea',10),
('press_to_email','','press_page','Send enquiries to (email address)','text',11),
('press_cc_email','','press_page','CC email (optional - comma-separate for more than one)','text',12)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

INSERT INTO `seo_meta` (`page_key`, `page_file`, `page_label`) VALUES
('press', 'press.php', 'Press & Media')
ON DUPLICATE KEY UPDATE `page_key` = `page_key`;
