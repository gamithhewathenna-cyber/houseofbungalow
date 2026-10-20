-- =====================================================================
-- New Contact page (contact.php) - name/address/email with a Google Map,
-- and points the footer's "CONTACT US" link at it.
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE is a no-op).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('contact_hero_image','assets/img/hero.jpg','contact_page','Hero image (poster/fallback)','image',1),
('contact_hero_video','','contact_page','Hero video (mp4/webm, ideally 1280×720)','video',1),
('contact_heading','Contact','contact_page','Heading','text',2),
('contact_subheading','','contact_page','Subheading','text',3),
('contact_lede','','contact_page','Lede paragraph (italic)','textarea',4),
('contact_name','House of Bungalow','contact_page','Business name','text',5),
('contact_address_line1','65-67 Foveaux St','contact_page','Address line 1','text',6),
('contact_address_line2','Surry Hills NSW 2010','contact_page','Address line 2','text',7),
('contact_email','hello@houseofbungalow.com.au','contact_page','Email address','text',8),
('contact_map_address','65-67 Foveaux St, Surry Hills NSW 2010','contact_page','Map address (used to look up the Google Map)','text',9)
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- Point the existing footer "CONTACT US" link at the new page.
UPDATE `blocks` SET `link_url` = 'contact.php' WHERE `block_type` = 'footer_nav' AND `title` = 'CONTACT US';
