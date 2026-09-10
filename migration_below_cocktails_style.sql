-- =====================================================================
-- House of Bungalow — Below "Cocktails" section restyle migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add the bold-word markers ("**word**") used to bold
-- "8:30 PM" and "Wednesday – Sunday" on the Below page.
-- Safe to run more than once.
-- =====================================================================

UPDATE `settings` SET `svalue` = 'From **8:30 PM**, the DJ takes over.' WHERE `skey` = 'below_cocktails_p2';
UPDATE `settings` SET `svalue` = '**Wednesday – Sunday** · From 5 PM until late.' WHERE `skey` = 'below_cocktails_hours';
