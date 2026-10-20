-- =====================================================================
-- House of Bungalow — What's On "BELOW" tag button migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to make the BELOW tag button on the What's On page smooth-
-- scroll down to the "See This Week's Line-Up" section on the same
-- page, instead of navigating to the Below page.
-- Only updates the URL if it was never customised away from the
-- original "below.php" value — safe to run more than once.
-- =====================================================================

UPDATE `settings`
SET `svalue` = '#lineup-section',
    `label`  = 'Tag button 3 URL (use #lineup-section to scroll to the Line-Up section on this page)'
WHERE `skey` = 'wo_tag3_url' AND `section` = 'whatson_page' AND `svalue` = 'below.php';
