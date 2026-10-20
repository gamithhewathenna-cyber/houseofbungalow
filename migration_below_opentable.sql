-- =====================================================================
-- Adds the OpenTable reservation widget to the Below page, overlaid on
-- the hero video/image the same way as the Restaurant page. Editable
-- from admin > Below > Hero Video tab - paste a new embed code there
-- to change it, or clear the field to hide it.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE below is a no-op
-- on repeat runs - if you've already customised the embed code from the
-- admin, this won't overwrite it).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('below_opentable_embed','<script type=''text/javascript'' src=''//www.opentable.com.au/widget/reservation/loader?rid=331247&type=standard&theme=standard&color=8&dark=false&iframe=true&domain=comau&lang=en-AU&newtab=true&ot_source=Restaurant%20website&font=arial&ot_logo=standard&primary_color=401316&primary_font_color=ffffff&button_color=E63349&button_font_color=ffffff&logo_pid=97211862&background_pid=97211845&cfe=true''></script>','below','OpenTable reservation widget (embed code - overlaid on the hero; leave blank to hide)','textarea',1)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
