-- =====================================================================
-- House of Bungalow — Private Events: back to 2 cards
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to remove the "Café Events" and "Full House Events" cards
-- added earlier, leaving only "Restaurant Events" and "Below Events".
-- Safe to run more than once (DELETE is a no-op if the rows are gone).
-- =====================================================================

DELETE FROM `blocks`
WHERE `block_type` = 'pe_event_card' AND `title` IN ('Café Events', 'Full House Events');
