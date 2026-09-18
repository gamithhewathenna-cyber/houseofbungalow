-- =====================================================================
-- House of Bungalow — Scope the "BOOK A TABLE" popup to the Home page
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database.
--
-- The popup used to open from ANY button labelled exactly "BOOK A TABLE"
-- anywhere on the site (matched by text). It now only opens from the
-- Home page's button (enforced in code via a dedicated CSS trigger
-- class, not by label text) — every other "BOOK A TABLE" button keeps
-- its own page's existing backend field, and now defaults to linking to
-- the new Reserve page instead of a "#" placeholder.
--
-- Only updates a URL if it was never customised away from "#", so any
-- link you've already set manually is left untouched.
-- Safe to run more than once.
-- =====================================================================

UPDATE `settings` SET `svalue` = 'reserve.php'
  WHERE `skey` = 'rest_intro_btn_url' AND `section` = 'restaurant' AND `svalue` = '#';

UPDATE `settings` SET `svalue` = 'reserve.php'
  WHERE `skey` = 'rest_fridaysat_btn_url' AND `section` = 'restaurant' AND `svalue` = '#';

UPDATE `settings` SET `svalue` = 'reserve.php'
  WHERE `skey` = 'rest_hours_btn_url' AND `section` = 'restaurant' AND `svalue` = '#';

UPDATE `settings` SET `svalue` = 'reserve.php'
  WHERE `skey` = 'below_music_btn1_url' AND `section` = 'below' AND `svalue` = '#';

UPDATE `settings` SET `svalue` = 'reserve.php'
  WHERE `skey` = 'wo_friday_btn_url' AND `section` = 'whatson_page' AND `svalue` = '#';

UPDATE `settings` SET `svalue` = 'reserve.php'
  WHERE `skey` = 'dp_btn2_url' AND `section` = 'dinnerparty_page' AND `svalue` = '#';
