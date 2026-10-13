-- Removes the "VIEW MENU" button from the Café page's Hours & Location
-- section (now redundant since the Café page has its own Menu section).
-- "GET DIRECTIONS" is untouched. Clearing the label hides the button
-- without losing the saved URL, in case it's wanted back later.
UPDATE `settings` SET `svalue` = '' WHERE `skey` = 'cafe_btn1_label';
