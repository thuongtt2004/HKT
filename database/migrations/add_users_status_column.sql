-- Migration: Thêm cột status vào bảng users
-- Date: 2025-12-23
-- Chạy file này để thêm cột status cho việc khóa/mở khóa tài khoản

-- Thêm cột status vào bảng users
ALTER TABLE `users` 
ADD COLUMN `status` TINYINT(1) DEFAULT 1 AFTER `is_admin`;

-- Cập nhật tất cả user hiện tại có status = 1 (active)
UPDATE `users` SET `status` = 1 WHERE `status` IS NULL;

-- Thêm comment cho cột
ALTER TABLE `users` 
MODIFY COLUMN `status` TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=locked';
