-- Run this once on an existing item_db database before using the email field.
ALTER TABLE `users`
    ADD COLUMN `email` varchar(150) DEFAULT NULL AFTER `full_name`,
    ADD UNIQUE KEY `users_email_unique` (`email`);
