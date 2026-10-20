-- =====================================================================
-- Adds the OpenTable reservation widget to the Restaurant page, shown
-- above the hero video/image at the very top of the page. Editable
-- from admin > Restaurant & Menus > Hero Video tab - paste a new embed
-- code there to change it, or clear the field to hide it.
--
-- Run this once via cPanel > phpMyAdmin > SQL tab.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE below is a no-op
-- on repeat runs - if you've already customised the embed code from the
-- admin, this won't overwrite it).
-- =====================================================================

INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('rest_opentable_embed','<script type=''text/javascript'' src=''//www.opentable.com.au/widget/reservation/loader?rid=302468&type=standard&theme=standard&color=8&dark=false&iframe=true&domain=comau&lang=en-AU&newtab=true&ot_source=Restaurant%20website&font=arial&ot_logo=subtle&primary_color=401316&primary_font_color=ffffff&button_color=E63349&button_font_color=ffffff&logo_pid=97211936&background_pid=97211889&cfe=true''></script>','restaurant','OpenTable reservation widget (embed code - shown above the hero video; leave blank to hide)','textarea',1)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
