-- Update the admin label for door_p2 to let the admin know line breaks (Enter key or typing <br>) now work in that field.
UPDATE settings SET label = 'Paragraph 2 (type <br> or press Enter for a line break)' WHERE skey = 'door_p2';
