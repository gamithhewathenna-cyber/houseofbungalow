-- Adds the "Company" field to the Private Events ENQUIRE popup form,
-- shown right after Email address. Optional, so backfills to empty.
ALTER TABLE enquiries ADD COLUMN company VARCHAR(160) NOT NULL DEFAULT '' AFTER email;
