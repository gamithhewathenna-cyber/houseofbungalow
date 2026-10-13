-- Renames the Contact page's heading from "Contact" to "Contact Us"
-- (only touches it if it's still the original default, so it won't
-- overwrite a heading you've already customised).
UPDATE `settings` SET `svalue` = 'Contact Us' WHERE `skey` = 'contact_heading' AND `svalue` = 'Contact';
