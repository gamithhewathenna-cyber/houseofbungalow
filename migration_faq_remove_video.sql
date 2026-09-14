-- =====================================================================
-- House of Bungalow — FAQ page: remove Hero Video option
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database. The FAQ page hero is now a static image only — the Hero
-- Video tab and setting have been removed.
-- Safe to run more than once (DELETE is a no-op if already removed).
-- =====================================================================

DELETE FROM `settings` WHERE `skey` = 'faq_hero_video' AND `section` = 'faq_page';
