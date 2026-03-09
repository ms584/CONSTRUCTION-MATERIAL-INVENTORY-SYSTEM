-- 1. ตารางพนักงาน / ผู้ใช้งานระบบ (พนักงานขาย, ผู้จัดการ, แอดมิน)
CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Admin','Manager','Cashier','Stock') NOT NULL DEFAULT 'Cashier',
  PRIMARY KEY (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. ตารางหมวดหมู่สินค้า
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL, 
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. ตารางหน่วยนับ
CREATE TABLE `units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL, -- เช่น กระสอบ, เส้น, คิว, แผ่น, กก.
  PRIMARY KEY (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. ตารางยี่ห้อ (Brands)
CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(100) NOT NULL, -- เช่น SCG, TPI, TOA, ห้าห่วง
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. ตารางผู้จัดจำหน่าย (Suppliers / ร้านส่ง)
CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. ตารางลูกค้า (ผู้รับเหมา / ลูกค้าสมาชิก)
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. ตารางข้อมูลสินค้า / วัสดุก่อสร้าง (เชื่อมโยง หมวดหมู่, ยี่ห้อ, หน่วยนับ)
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) NOT NULL, 
  `product_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00, -- ทุนเฉลี่ย
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00, -- ราคาขายหน้าร้าน
  `stock_qty` int(11) NOT NULL DEFAULT 0, -- จำนวนคงเหลือในโกดัง
  `min_stock` int(11) NOT NULL DEFAULT 10, -- จุดแจ้งเตือนของใกล้หมด
  `product_image` varchar(255) DEFAULT 'default.jpg',
  PRIMARY KEY (`product_id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`),
  FOREIGN KEY (`brand_id`) REFERENCES `brands`(`brand_id`),
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- 8. ตารางหัวบิลรับเข้า (ซื้อของเข้าโกดัง)
CREATE TABLE `receiving` (
  `receive_id` int(11) NOT NULL AUTO_INCREMENT,
  `receive_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `supplier_id` int(11) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL, -- เลขบิลจากบริษัทแม่
  `employee_id` int(11) NOT NULL, -- พนักงานที่รับของ
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  PRIMARY KEY (`receive_id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`supplier_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. ตารางรายละเอียดรับเข้า (รายการสินค้าในบิลรับเข้า)
CREATE TABLE `receiving_detail` (
  `receive_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `receive_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL, 
  `cost_price` decimal(10,2) NOT NULL, 
  PRIMARY KEY (`receive_detail_id`),
  FOREIGN KEY (`receive_id`) REFERENCES `receiving`(`receive_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. ตารางหัวบิลขาย (เบิกของออก / ขายหน้าร้าน)
CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_no` varchar(50) NOT NULL, 
  `sale_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_id` int(11) DEFAULT NULL, 
  `employee_id` int(11) NOT NULL, 
  `payment_method` enum('Cash','Transfer','Credit') NOT NULL DEFAULT 'Cash',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`sale_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. ตารางรายละเอียดบิลขาย (รายการสินค้าในบิลขาย)
CREATE TABLE `sales_detail` (
  `sale_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL, -- จำนวนที่ขายออก (จะลบจาก stock_qty)
  `selling_price` decimal(10,2) NOT NULL, -- ราคาขาย ณ ตอนนั้น
  PRIMARY KEY (`sale_detail_id`),
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`sale_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `products` ADD `location` VARCHAR(100) NULL AFTER `unit_id`;
ALTER TABLE `sales` ADD `payment_status` VARCHAR(50) NOT NULL DEFAULT 'ชำระแล้ว' AFTER `payment_method`;