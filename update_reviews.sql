-- Добавляем поддержку ответов на отзывы
ALTER TABLE `reviews` ADD COLUMN `parent_id` INT NULL DEFAULT NULL AFTER `user_id`;
ALTER TABLE `reviews` ADD FOREIGN KEY (`parent_id`) REFERENCES `reviews`(`id`) ON DELETE CASCADE;

-- Добавляем индекс для быстрого поиска ответов
ALTER TABLE `reviews` ADD INDEX `idx_parent_id` (`parent_id`);

-- Добавляем поле для отслеживания времени редактирования
ALTER TABLE `reviews` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;
