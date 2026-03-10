-- =============================================
-- DISCOUNT SYSTEM - รันใน phpMyAdmin
-- =============================================

-- 1. สร้างตาราง coupons
CREATE TABLE IF NOT EXISTS `coupons` (
  `coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code` varchar(50) NOT NULL,
  `coupon_type` enum('cash','percent') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL COMMENT 'บาท (cash) หรือ % (percent)',
  `max_discount` decimal(10,2) DEFAULT NULL COMMENT 'cap สำหรับ percent เช่น 10000',
  `min_order` decimal(10,2) DEFAULT 0.00 COMMENT 'ยอดขั้นต่ำที่ใช้คูปองได้',
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `used_by_sale_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `coupon_code` (`coupon_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. เพิ่ม column ส่วนลดใน sales
ALTER TABLE `sales`
  ADD `subtotal_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ยอดก่อนลด' AFTER `total_amount`,
  ADD `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ยอดส่วนลด' AFTER `subtotal_amount`,
  ADD `discount_type` varchar(100) DEFAULT NULL COMMENT 'รายละเอียดส่วนลด' AFTER `discount_amount`,
  ADD `coupon_id` int(11) DEFAULT NULL COMMENT 'คูปองที่ใช้' AFTER `discount_type`;

-- 3. ข้อมูลตัวอย่างคูปอง (optional)
INSERT INTO `coupons` (`coupon_code`, `coupon_type`, `discount_value`, `max_discount`, `min_order`) VALUES
('CASH100', 'cash', 100.00, NULL, 0.00),
('CASH500', 'cash', 500.00, NULL, 0.00),
('CASH1000', 'cash', 1000.00, NULL, 0.00),
('PCT10', 'percent', 10.00, 10000.00, 0.00);
