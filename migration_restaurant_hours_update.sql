-- Updates the Restaurant page's "Restaurant Hours" section to:
--   Tuesday - Sunday : 11:30am until late
--   Monday: Closed
--   [BOOK A TABLE]
-- Clears the separate Friday & Saturday line since Tuesday-Sunday now covers it.
UPDATE `settings` SET `svalue` = 'Tuesday - Sunday' WHERE `skey` = 'rest_hours_lunch_label';
UPDATE `settings` SET `svalue` = '11:30am until late' WHERE `skey` = 'rest_hours_lunch';
UPDATE `settings` SET `svalue` = 'Monday' WHERE `skey` = 'rest_hours_dinner_label';
UPDATE `settings` SET `svalue` = 'Closed' WHERE `skey` = 'rest_hours_dinner';
UPDATE `settings` SET `svalue` = '' WHERE `skey` = 'rest_hours_fridaysat';
