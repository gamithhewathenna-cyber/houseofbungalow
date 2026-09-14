-- =====================================================================
-- House of Bungalow — Replace em dash (—) with a plain hyphen (-)
-- Run this once via cPanel > phpMyAdmin > SQL tab, against the existing
-- database, to clean up every "—" already stored in settings/blocks
-- content (page text, labels, admin field descriptions) — the PHP/CSS
-- source files have already been updated the same way.
-- Safe to run more than once (REPLACE is a no-op once no "—" remain).
-- =====================================================================

UPDATE `settings` SET `svalue` = REPLACE(`svalue`, '—', '-') WHERE `svalue` LIKE '%—%';
UPDATE `settings` SET `label`  = REPLACE(`label`, '—', '-')  WHERE `label`  LIKE '%—%';

UPDATE `blocks` SET `title`    = REPLACE(`title`, '—', '-')    WHERE `title`    LIKE '%—%';
UPDATE `blocks` SET `subtitle` = REPLACE(`subtitle`, '—', '-') WHERE `subtitle` LIKE '%—%';
UPDATE `blocks` SET `body`     = REPLACE(`body`, '—', '-')     WHERE `body`     LIKE '%—%';
