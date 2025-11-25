-- Thêm cột google_id và google_picture vào bảng users
-- Chạy SQL này trong phpMyAdmin hoặc MySQL client

ALTER TABLE `users` 
ADD COLUMN `google_id` VARCHAR(255) NULL DEFAULT NULL AFTER `password`,
ADD COLUMN `google_picture` VARCHAR(500) NULL DEFAULT NULL AFTER `google_id`,
ADD UNIQUE INDEX `google_id_UNIQUE` (`google_id` ASC);

-- Kiểm tra cột đã được thêm
DESCRIBE users;
