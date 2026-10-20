-- =====================================================================
-- VIP Bottle Service popup (Below page - "BOOK BOTTLE SERVICE" button)
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE is a no-op for
-- settings; CREATE TABLE IF NOT EXISTS is a no-op for the table).
-- =====================================================================

CREATE TABLE IF NOT EXISTS `vip_bottle_enquiries` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `company` VARCHAR(160) NOT NULL DEFAULT '',
  `occasion` VARCHAR(80) NOT NULL DEFAULT '',
  `guests` VARCHAR(20) NOT NULL DEFAULT '',
  `preferred_date` VARCHAR(40) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('below_vipbottle_popup_eyebrow1','Below','below','VIP Bottle Service popup - eyebrow line 1','text',49),
('below_vipbottle_popup_eyebrow2','VIP Bottle Service','below','VIP Bottle Service popup - eyebrow line 2','text',50),
('below_vipbottle_popup_heading','Your Table. Your Experience.','below','VIP Bottle Service popup - heading','text',51),
('below_vipbottle_popup_subheading','Make yourself at home. We''ll take care of the rest.','below','VIP Bottle Service popup - subheading','text',52),
('below_vipbottle_popup_p1','Enjoy Below with your own VIP table, dedicated service and a bottle-service experience tailored to your occasion.','below','VIP Bottle Service popup - paragraph 1','textarea',53),
('below_vipbottle_popup_p2','Leave your details below and our team will be in touch.','below','VIP Bottle Service popup - paragraph 2','textarea',54),
('below_vipbottle_popup_success','Thank you for your enquiry. Our VIP team will be in touch to discuss table availability and bottle-service options.','below','VIP Bottle Service popup - thank-you message (shown after submitting)','textarea',55),
('below_vipbottle_enquiry_to_email','','below','VIP Bottle Service - send enquiries to (email address)','text',56),
('below_vipbottle_enquiry_cc_email','','below','VIP Bottle Service - CC email (optional - comma-separate for more than one)','text',57)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
