-- Добавляем поле для отслеживания времени редактирования отзывов
ALTER TABLE `reviews` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;
