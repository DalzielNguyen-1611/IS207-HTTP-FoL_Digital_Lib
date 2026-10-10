-- ====================================================================
-- TECHCARE PLATFORM SEED DATA (MYSQL)
-- Dữ liệu mẫu khởi tạo chuẩn IT Support & Sửa Chữa Máy Tính
-- Khớp 100% với giao diện TechCare Desk (PC, Laptop, Mạng, Ngoại vi)
-- ====================================================================

USE `techcare_db`;

-- Xóa dữ liệu cũ trước khi nạp
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM `reviews`;
DELETE FROM `wallet_transactions`;
DELETE FROM `wallets`;
DELETE FROM `booking_parts`;
DELETE FROM `bookings`;
DELETE FROM `services`;
DELETE FROM `technicians`;
DELETE FROM `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. INSERT USERS (Mật khẩu mặc định: 123456 được băm chuẩn bcrypt)
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `phone`) VALUES
('USR-000', 'admin', 'admin@techcare.vn', '$2y$10$x..smPnT.gbSYZ/Y.7yaNOACjEKlNFjUYSAWe14Roti689nCy2Ql.', 'Quản Trị Viên Hệ Thống', 'admin', '0909999999'),
('USR-001', 'hoangnam', 'hoangnam@techcare.vn', '$2y$10$x..smPnT.gbSYZ/Y.7yaNOACjEKlNFjUYSAWe14Roti689nCy2Ql.', 'Hoàng Nam', 'dispatcher', '0901888888'),
('USR-002', 'vanminh', 'vanminh@techcare.vn', '$2y$10$x..smPnT.gbSYZ/Y.7yaNOACjEKlNFjUYSAWe14Roti689nCy2Ql.', 'Nguyễn Văn Minh', 'technician', '0988123456'),
('USR-003', 'minhanh', 'minhanh@gmail.com', '$2y$10$x..smPnT.gbSYZ/Y.7yaNOACjEKlNFjUYSAWe14Roti689nCy2Ql.', 'Chị Minh Anh', 'customer', '0912345678'),
('USR-004', 'dinhtrong', 'dinhtrong@techcare.vn', '$2y$10$x..smPnT.gbSYZ/Y.7yaNOACjEKlNFjUYSAWe14Roti689nCy2Ql.', 'Trần Đình Trọng', 'technician', '0977456789'),
('USR-005', 'lelong', 'lelong@techcare.vn', '$2y$10$x..smPnT.gbSYZ/Y.7yaNOACjEKlNFjUYSAWe14Roti689nCy2Ql.', 'Lê Hoàng Long', 'technician', '0933111222');

-- 2. INSERT TECHNICIANS
INSERT INTO `technicians` (`id`, `code`, `user_id`, `level`, `specialty`, `rating`, `review_count`, `sla_success_rate`, `shift_code`, `shift_status`, `current_address`) VALUES
('TECH-204', 'NV-204', 'USR-002', 'Bậc 3', 'Phần Cứng & Mạng Máy Tính', 4.9, 142, 98.40, '#A1', 'ready', 'Quận 7, TP.HCM'),
('TECH-108', 'NV-108', 'USR-004', 'Bậc 2', 'Mạng Doanh Nghiệp & Máy Chủ', 4.8, 98, 96.20, '#A2', 'busy', 'Quận 1, TP.HCM'),
('TECH-105', 'NV-105', 'USR-005', 'Bậc 3', 'Hạ Tầng IT & Bảo Mật Hệ Thống', 4.9, 115, 99.10, '#A1', 'ready', 'Quận Bình Thạnh, TP.HCM');

-- 3. INSERT SERVICES (IT Support Services)
INSERT INTO `services` (`id`, `platform`, `name`, `category`, `base_price`, `estimated_duration_minutes`) VALUES
('SRV-01', 'TechCare Desk', 'Cài Đặt Hệ Điều Hành & Cứu Dữ Liệu', 'Phần Mềm', 200000.00, 45),
('SRV-02', 'TechCare Desk', 'Vệ Sinh, Bảo Dưỡng & Nâng Cấp Phần Cứng', 'Phần Cứng', 250000.00, 60),
('SRV-03', 'TechCare Desk', 'Khắc Phục Sự Cố BSOD & Thay Thế Linh Kiện', 'Phần Cứng', 350000.00, 60),
('SRV-04', 'TechCare Desk', 'Cứu Hộ Mạng LAN / WiFi / Router Khẩn Cấp', 'Hạ Tầng Mạng', 500000.00, 60);

-- 4. INSERT BOOKINGS (IT Support Orders)
INSERT INTO `bookings` (`id`, `code`, `platform`, `service_id`, `customer_id`, `customer_name`, `customer_phone`, `address`, `device_type`, `issue_description`, `scheduled_time`, `priority`, `status`, `assigned_technician_id`, `estimated_cost`, `actual_cost`) VALUES
('BK-3895', 'TC-DESK-3895', 'TechCare Desk', 'SRV-02', 'USR-003', 'Chị Minh Anh', '0912 345 678', 'Căn hộ 1204, Tháp B2, Chung cư Sunrise City, Nguyễn Hữu Thọ, P. Tân Hưng, Q.7', 'Laptop Dell XPS 15 (9500)', 'Laptop báo chai pin 85%, máy nóng tự sập nguồn khi render, quạt tản nhiệt kêu to. Đã thay pin mới và bảo dưỡng tra keo tản nhiệt.', '30/09/2026 • 10:00 - 11:30', 'high', 'completed', 'TECH-204', 850000.00, 950000.00),
('BK-4523', 'TC-HOME-4523', 'TechCare Home', 'SRV-04', 'USR-003', 'Chị Minh Anh', '0912 345 678', 'Căn hộ 1204, Tháp B2, Chung cư Sunrise City, Nguyễn Hữu Thọ, P. Tân Hưng, Q.7', 'Mạng & Wifi', 'Mất kết nối internet toàn văn phòng, đèn modem Router chớp đỏ, cần kỹ thuật viên IT kiểm tra đường truyền và bấm lại đầu dây cáp RJ45.', '01/10/2026 • 08:30 - 10:00 (Khẩn cấp)', 'urgent', 'assigned', 'TECH-204', 350000.00, NULL),
('BK-4720', 'TC-HOME-4720', 'TechCare Home', 'SRV-04', 'USR-003', 'Chị Minh Anh', '0912 345 678', 'Căn hộ 1204, Tháp B2, Chung cư Sunrise City, Nguyễn Hữu Thọ, P. Tân Hưng, Q.7', 'Mạng & Wifi', 'Sóng Wifi chập chờn, thường xuyên rớt mạng khi họp trực tuyến, cần KTV khảo sát và cấu hình bộ phát Router cân bằng tải.', '01/10/2026 • 14:00 - 15:30 (Tiêu chuẩn)', 'normal', 'pending', NULL, 250000.00, NULL),
('BK-1357', 'TC-HOME-1357', 'TechCare Home', 'SRV-03', 'USR-003', 'Chị Minh Anh', '0912 345 678', 'Căn hộ 1204, Tháp B2, Chung cư Sunrise City, Nguyễn Hữu Thọ, P. Tân Hưng, Q.7', 'Laptop / MacBook', 'Laptop Dell bật không lên nguồn, quạt quay mạnh nhưng màn hình đen, cần kỹ thuật viên IT kiểm tra và xử lý gấp.', '01/10/2026 • 08:30 - 10:00 (Khẩn cấp)', 'urgent', 'pending', NULL, 350000.00, NULL),
('BK-1029', 'TC-DESK-1029', 'TechCare Desk', 'SRV-04', NULL, 'Công ty Cổ phần Công nghệ ABC', '028 3822 9999', 'Tầng 8, Tòa nhà Bitexco, Q.1, TP.HCM', 'Cisco Router & Switch Core Văn Phòng', 'Mạng nội bộ mất kết nối internet toàn văn phòng, hệ thống VPN chi nhánh bị ngắt.', 'Ngay lập tức (SLA ≤ 30 phút)', 'urgent', 'in_progress', 'TECH-108', 450000.00, NULL),
('BK-3896', 'TC-DESK-3896', 'TechCare Desk', 'SRV-03', NULL, 'Anh Quốc Huy', '0903 678 910', 'Số 45 Lê Văn Lương, Tân Kiểng, Quận 7', 'PC Gaming Core i7 / RTX 3060', 'Máy tính liên tục bị lỗi màn hình xanh (BSOD WHEA_UNCORRECTABLE_ERROR), tự khởi động lại khi chơi game.', '14:00 - 15:30 Chiều nay', 'normal', 'pending', NULL, 300000.00, NULL);

-- 5. INSERT BOOKING PARTS FOR #TC-DESK-3895 (Laptop Dell XPS)
INSERT INTO `booking_parts` (`id`, `booking_id`, `part_name`, `quantity`, `unit_price`, `total_price`) VALUES
('PRT-01', 'BK-3895', 'Pin Laptop Dell XPS 15 86Wh chính hãng', 1, 650000.00, 650000.00),
('PRT-02', 'BK-3895', 'Keo tản nhiệt cao cấp Arctic MX-4 (4g)', 1, 150000.00, 150000.00),
('PRT-03', 'BK-3895', 'Công kỹ thuật vệ sinh quạt & kiểm tra phần cứng', 1, 150000.00, 150000.00);

-- 6. INSERT WALLETS & TRANSACTIONS
INSERT INTO `wallets` (`id`, `technician_id`, `balance`, `pending_balance`, `bank_name`, `bank_account_number`, `bank_account_name`) VALUES
('WAL-204', 'TECH-204', 3450000.00, 950000.00, 'Vietcombank', '0071001234567', 'NGUYEN VAN MINH');

INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `booking_id`, `type`, `title`, `amount`, `status`) VALUES
('TXN-901', 'WAL-204', 'BK-3895', 'income', 'Hoa hồng xử lý sự cố Laptop Dell #TC-DESK-3895', 320000.00, 'success'),
('TXN-902', 'WAL-204', NULL, 'income', 'Hoa hồng cài đặt & tối ưu hệ thống PC #TC-DESK-3888', 450000.00, 'success'),
('TXN-903', 'WAL-204', NULL, 'withdraw', 'Rút tiền về Vietcombank (*4567)', -2000000.00, 'success');

-- 7. INSERT REVIEWS
INSERT INTO `reviews` (`id`, `booking_id`, `technician_id`, `customer_id`, `rating`, `comment`) VALUES
('REV-01', 'BK-3895', 'TECH-204', 'USR-003', 5, 'Kỹ thuật viên Minh đến rất đúng giờ, hỗ trợ kiểm tra laptop Dell rất chuyên nghiệp, xử lý xong máy chạy mát và không còn sập nguồn!');
