-- =====================================================================
-- House of Bungalow — Below page "WHAT'S ON" button migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to make the WHAT'S ON button in the Below page's intro
-- smooth-scroll down to the "See This Week's Line-Up" section on the
-- same page, instead of going nowhere ("#").
-- Only updates the URL if it was never customised away from "#" — safe
-- to run more than once.
-- =====================================================================

UPDATE `settings`
SET `svalue` = '#lineup-section',
    `label`  = 'Intro - button 1 URL (use #lineup-section to scroll to the Line-Up section on this page)'
WHERE `skey` = 'below_intro_btn1_url' AND `section` = 'below' AND `svalue` = '#';
