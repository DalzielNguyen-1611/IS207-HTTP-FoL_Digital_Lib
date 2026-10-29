<?php

declare(strict_types=1);

namespace App\Validators;

/**
 * Kiểm tra và chuẩn hóa dữ liệu đầu vào xác thực (Auth Validator)
 */
class AuthValidator
{
    /**
     * Xác thực thông tin đăng nhập
     * @return array<string, string> Mảng lỗi nếu có (rỗng nghĩa là hợp lệ)
     */
    public static function validateLogin(array $input): array
    {
        $errors = [];

        $email = trim((string)($input['email'] ?? ''));
        if ($email === '') {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Địa chỉ email không đúng định dạng.';
        }

        $password = (string)($input['mat_khau'] ?? $input['password'] ?? '');
        if ($password === '') {
            $errors['password'] = 'Mật khẩu không được để trống.';
        }

        return $errors;
    }
}
