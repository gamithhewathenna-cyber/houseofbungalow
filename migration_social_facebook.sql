-- Swaps the footer's Spotify icon/link for Facebook. Renames the existing
-- setting in place so any URL already saved there is preserved as the
-- starting value (update it in the admin afterwards to the real Facebook URL).
UPDATE `settings` SET `skey` = 'social_facebook', `label` = 'Facebook URL' WHERE `skey` = 'social_spotify';
