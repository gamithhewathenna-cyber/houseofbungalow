-- Re-adds the Hero Video option to the FAQ page (this reverses the
-- earlier migration_faq_remove_video.sql). Safe to run more than once
-- (ON DUPLICATE KEY UPDATE is a no-op).
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('faq_hero_video','','faq_page','Hero video (mp4/webm, ideally 1280×720)','video',1)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
