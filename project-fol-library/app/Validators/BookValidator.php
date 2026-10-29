<?php

declare(strict_types=1);

namespace App\Validators;

use App\Repositories\BookRepository;

/**
 * Kiểm tra và chuẩn hóa dữ liệu đầu vào cho Đầu sách (Book Validator)
 * Tuân thủ Rule 8, 9, 11 (AGENTS.md)
 */
class BookValidator
{
    /**
     * Xác thực thông tin thêm sách mới
     *
     * @return array<string, string> Mảng lỗi nếu có (rỗng nghĩa là dữ liệu hợp lệ)
     */
    public static function validateCreate(array $data, ?BookRepository $repo = null): array
    {
        $errors = [];

        // 1. Tiêu đề sách
        $title = trim((string)($data['tieu_de'] ?? ''));
        if ($title === '') {
            $errors['tieu_de'] = 'Tên sách không được để trống.';
        } elseif (mb_strlen($title) < 2 || mb_strlen($title) > 300) {
            $errors['tieu_de'] = 'Tên sách phải có từ 2 đến 300 ký tự.';
        }

        // 2. Tác giả
        $author = trim((string)($data['tac_gia'] ?? ''));
        if ($author === '') {
            $errors['tac_gia'] = 'Tác giả không được để trống.';
        } elseif (mb_strlen($author) < 2 || mb_strlen($author) > 250) {
            $errors['tac_gia'] = 'Tên tác giả phải có từ 2 đến 250 ký tự.';
        }

        // 3. Năm xuất bản
        if (!empty($data['nam_xuat_ban'])) {
            $year = filter_var($data['nam_xuat_ban'], FILTER_VALIDATE_INT);
            $currentYear = (int)date('Y') + 1;
            if ($year === false || $year < 1000 || $year > $currentYear) {
                $errors['nam_xuat_ban'] = "Năm xuất bản phải là số nguyên hợp lệ (1000 - {$currentYear}).";
            }
        }

        // 4. Số lượng cuốn sách
        if (isset($data['so_luong'])) {
            $copies = filter_var($data['so_luong'], FILTER_VALIDATE_INT);
            if ($copies === false || $copies < 1 || $copies > 1000) {
                $errors['so_luong'] = 'Số lượng bản sách ban đầu phải từ 1 đến 1.000 cuốn.';
            }
        }

        // 5. Kiểm tra mã đầu sách nếu người dùng tự nhập
        $id = trim((string)($data['id_dau_sach'] ?? ''));
        if ($id !== '') {
            if (!preg_match('/^[A-Za-z0-9\-_]{3,50}$/', $id)) {
                $errors['id_dau_sach'] = 'Mã sách chỉ được chứa chữ cái, số, dấu gạch nối (-) hoặc gạch dưới (_), độ dài 3-50 ký tự.';
            } elseif ($repo && $repo->exists($id)) {
                $errors['id_dau_sach'] = "Mã sách '{$id}' đã tồn tại trong hệ thống.";
            }
        }

        return $errors;
    }

    /**
     * Xác thực thông tin cập nhật sách
     *
     * @return array<string, string> Mảng lỗi nếu có
     */
    public static function validateUpdate(array $data): array
    {
        $errors = [];

        // 1. Tiêu đề sách
        $title = trim((string)($data['tieu_de'] ?? ''));
        if ($title === '') {
            $errors['tieu_de'] = 'Tên sách không được để trống.';
        } elseif (mb_strlen($title) < 2 || mb_strlen($title) > 300) {
            $errors['tieu_de'] = 'Tên sách phải có từ 2 đến 300 ký tự.';
        }

        // 2. Tác giả
        $author = trim((string)($data['tac_gia'] ?? ''));
        if ($author === '') {
            $errors['tac_gia'] = 'Tác giả không được để trống.';
        } elseif (mb_strlen($author) < 2 || mb_strlen($author) > 250) {
            $errors['tac_gia'] = 'Tên tác giả phải có từ 2 đến 250 ký tự.';
        }

        // 3. Năm xuất bản
        if (!empty($data['nam_xuat_ban'])) {
            $year = filter_var($data['nam_xuat_ban'], FILTER_VALIDATE_INT);
            $currentYear = (int)date('Y') + 1;
            if ($year === false || $year < 1000 || $year > $currentYear) {
                $errors['nam_xuat_ban'] = "Năm xuất bản phải là số nguyên hợp lệ (1000 - {$currentYear}).";
            }
        }

        // 4. Số lượng bổ sung (nếu có)
        if (isset($data['additional_copies']) && $data['additional_copies'] !== '') {
            $add = filter_var($data['additional_copies'], FILTER_VALIDATE_INT);
            if ($add === false || $add < 0 || $add > 500) {
                $errors['additional_copies'] = 'Số lượng bản sách bổ sung phải từ 0 đến 500 cuốn.';
            }
        }

        return $errors;
    }
}
