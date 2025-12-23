-- Migration: Thêm các trạng thái trả hàng mới vào bảng orders
-- Date: 2025-12-23
-- Chạy file này để cập nhật database với các trạng thái:
-- 1. Chờ xác nhận trả hàng (không khóa)
-- 2. Đã duyệt trả hàng (khóa)
-- 3. Không đồng ý duyệt trả hàng (khóa)

-- Sửa lại enum order_status để thêm các trạng thái trả hàng
ALTER TABLE `orders` 
MODIFY COLUMN `order_status` ENUM(
    'Chờ thanh toán',
    'Chờ xác nhận', 
    'Đã xác nhận',
    'Đang giao',
    'Hoàn thành',
    'Đã hủy',
    'Chờ xác nhận trả hàng',
    'Đã duyệt trả hàng',
    'Không đồng ý duyệt trả hàng'
) DEFAULT 'Chờ xác nhận';

-- Cập nhật các đơn hàng có return_request = 1 và return_status = 'Chờ duyệt' 
-- thành trạng thái 'Chờ xác nhận trả hàng'
UPDATE `orders` 
SET `order_status` = 'Chờ xác nhận trả hàng' 
WHERE `return_request` = 1 
AND (`return_status` = 'Chờ duyệt' OR `return_status` IS NULL);

-- Cập nhật các đơn hàng đã được duyệt trả hàng
UPDATE `orders` 
SET `order_status` = 'Đã duyệt trả hàng' 
WHERE `return_request` = 1 
AND `return_status` = 'Đã duyệt';

-- Cập nhật các đơn hàng bị từ chối trả hàng
UPDATE `orders` 
SET `order_status` = 'Không đồng ý duyệt trả hàng' 
WHERE `return_request` = 1 
AND `return_status` = 'Từ chối';
