-- Add status column to categories table for admin management
ALTER TABLE `categories` 
ADD COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active' AFTER `description`;

-- Update existing categories to have active status
UPDATE `categories` SET `status` = 'active' WHERE `status` IS NULL;