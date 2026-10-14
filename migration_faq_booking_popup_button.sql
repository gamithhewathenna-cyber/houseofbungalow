-- =====================================================================
-- Adds a "BOOK A TABLE" button to the "Where is House of Bungalow?"
-- question on the FAQ page. Clicking it opens the Book A Table popup
-- (same popup as the header's RESERVE button), instead of going to a page.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once.
-- =====================================================================

UPDATE `blocks` SET `subtitle` = '1', `link_url2` = 'BOOK A TABLE', `link_url` = '#popup'
  WHERE `block_type` = 'faq_page_item' AND `title` = 'Where is House of Bungalow?';
