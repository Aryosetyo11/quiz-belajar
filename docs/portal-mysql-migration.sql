-- MySQL 8.0+; apply once after the existing Laravel users migration.
-- Existing user rows receive the safe default role "siswa" and no NISN.
ALTER TABLE `users`
    MODIFY `email` VARCHAR(255) NULL,
    ADD `role` ENUM('guru', 'siswa') NOT NULL DEFAULT 'siswa' AFTER `email_verified_at`,
    ADD `nisn` CHAR(10) NULL AFTER `role`,
    ADD INDEX `users_role_index` (`role`),
    ADD UNIQUE INDEX `users_nisn_unique` (`nisn`);

-- Passwords stay in the existing `password` column (VARCHAR(255)).
-- Store only Laravel Hash::make() output; never put plaintext passwords in SQL.
