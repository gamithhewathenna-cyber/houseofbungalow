-- Switches the Café page's gallery from a fixed 3-photo grid to the same
-- unlimited, admin-manageable gallery used on the Restaurant page.
-- Carries over whatever photos are currently set as the Café's 3 fixed
-- gallery images into the new "cafe_gallery" block collection.
INSERT INTO blocks (block_type, title, image, sort, active)
SELECT 'cafe_gallery', '', svalue, 1, 1 FROM settings WHERE skey = 'cafe_gallery_image1' AND svalue <> '';

INSERT INTO blocks (block_type, title, image, sort, active)
SELECT 'cafe_gallery', '', svalue, 2, 1 FROM settings WHERE skey = 'cafe_gallery_image2' AND svalue <> '';

INSERT INTO blocks (block_type, title, image, sort, active)
SELECT 'cafe_gallery', '', svalue, 3, 1 FROM settings WHERE skey = 'cafe_gallery_image3' AND svalue <> '';
