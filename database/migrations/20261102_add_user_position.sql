-- Run this once on an existing item_db database before using the registration form.
ALTER TABLE `users`
    ADD COLUMN `position` varchar(100) DEFAULT NULL AFTER `employment_type_id`;
