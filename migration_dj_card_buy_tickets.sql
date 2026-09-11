-- =====================================================================
-- House of Bungalow — DJ / Line-up card "Buy Tickets" button migration
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to add a second, separately-linked button to each DJ card
-- on the Below / What's On "See This Week's Line-Up" section.
-- Each card now shows "Book Now" (existing link_url column) and
-- "Buy Tickets" (new link_url2 column) — leave a link empty in the
-- admin to hide that button on a given card.
-- Not safe to run twice as-is (ALTER TABLE ADD COLUMN errors if the
-- column already exists) — if you've already run this once, skip it.
-- =====================================================================

ALTER TABLE `blocks` ADD COLUMN `link_url2` VARCHAR(255) NULL AFTER `link_url`;
