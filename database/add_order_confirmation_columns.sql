-- Thêm các cột cho xác nhận đơn hàng và yêu cầu trả hàng
ALTER TABLE `orders`
ADD COLUMN `customer_confirmed` TINYINT(1) DEFAULT 0 COMMENT 'Khách đã xác nhận nhận hàng (0=chưa, 1=đã xác nhận)',
ADD COLUMN `return_request` TINYINT(1) DEFAULT 0 COMMENT 'Yêu cầu trả hàng (0=không, 1=có yêu cầu)',
ADD COLUMN `return_reason` TEXT NULL COMMENT 'Lý do trả hàng',
ADD COLUMN `return_request_date` DATETIME NULL COMMENT 'Ngày yêu cầu trả hàng',
ADD COLUMN `return_status` VARCHAR(50) NULL COMMENT 'Trạng thái yêu cầu: Chờ duyệt, Đã duyệt, Từ chối',
ADD COLUMN `completed_date` DATETIME NULL COMMENT 'Ngày hoàn thành đơn hàng';

-- Kiểm tra cột đã được thêm
DESCRIBE orders;
