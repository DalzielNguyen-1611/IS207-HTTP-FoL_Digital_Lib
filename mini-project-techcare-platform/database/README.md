# Hướng Dẫn Cơ Sở Dữ Liệu (Database Guide)

Tầng Dữ Liệu (Data Tier) của đồ án TechCare Platform được thiết kế theo chuẩn CSDL quan hệ chuẩn hóa (3NF).

---

## 1. Cấu Trúc Các File CSDL
- `schema.sql`: Chứa mã DDL tạo toàn bộ 8 bảng quan hệ (Users, Technicians, Services, Bookings, Booking Parts, Wallets, Transactions, Reviews) cùng các ràng buộc khóa chính, khóa ngoại.
- `seed.sql` *(dữ liệu mẫu nội bộ)*: Chứa dữ liệu mẫu mô phỏng ca trực thực tế, tài khoản demo khớp với giao diện (file này được loại trừ khỏi repository chính thức).

---

## 2. Hướng Dẫn Nhập CSDL (Import Database)

### Cách 1: Sử dụng SQLite (Đơn giản nhất, không cần cài đặt server)
```bash
sqlite3 techcare.db < schema.sql
sqlite3 techcare.db < seed.sql
```

### Cách 2: Sử dụng MySQL (XAMPP / MySQL Workbench / DBeaver)
1. Mở phpMyAdmin hoặc MySQL CLI:
```sql
CREATE DATABASE techcare_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techcare_db;
```
2. Nhập file `schema.sql` rồi đến `seed.sql`.

### Cách 3: Sử dụng PostgreSQL
```bash
psql -U postgres -d techcare_db -f schema.sql
psql -U postgres -d techcare_db -f seed.sql
```

---

## 3. Sơ Đồ Quan Hệ Thực Thể (ERD Tóm Tắt)
- `users` (1) ──── (1) `technicians`
- `technicians` (1) ──── (N) `bookings`
- `bookings` (1) ──── (N) `booking_parts`
- `technicians` (1) ──── (1) `wallets`
- `wallets` (1) ──── (N) `wallet_transactions`
- `bookings` (1) ──── (1) `reviews`
