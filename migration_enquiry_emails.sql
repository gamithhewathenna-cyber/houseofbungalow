-- Adds admin-configurable settings for the Private Events ENQUIRE form:
-- where enquiry notifications are sent, an optional CC address, and the
-- subject/message of the thank-you email sent back to the client. No SMTP
-- setup needed - sending uses the server's own PHP mail() function.
-- Safe to run more than once (ON DUPLICATE KEY UPDATE is a no-op).
INSERT INTO `settings` (`skey`,`svalue`,`section`,`label`,`field_type`,`sort`) VALUES
('pe_enquiry_to_email','','privateevents_page','Send enquiries to (email address)','text',14),
('pe_enquiry_cc_email','','privateevents_page','CC email (optional - comma-separate for more than one)','text',15),
('pe_enquiry_thankyou_subject','Thanks for your enquiry - House of Bungalow','privateevents_page','Thank-you email subject (sent to the client)','text',16),
('pe_enquiry_thankyou_message','Hi {name},\n\nThanks for reaching out about your event - we''ve received your enquiry and someone from our team will be in touch shortly.\n\nIn the meantime, if you need anything, just reply to this email.\n\nSee you soon,\nHouse of Bungalow','privateevents_page','Thank-you email message (sent to the client - use {name} for their name)','textarea',17)
ON DUPLICATE KEY UPDATE `skey` = `skey`;
