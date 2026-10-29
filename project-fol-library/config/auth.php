<?php

declare(strict_types=1);

return [
    'google' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'] ?? getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:8000/auth/google/callback',
        // Danh sách domain email trường được phép đăng ký/đăng nhập tài khoản sinh viên
        'student_domains' => array_filter(array_map('trim', explode(',', strtolower((string)($_ENV['GOOGLE_STUDENT_DOMAINS'] ?? 'gm.uit.edu.vn,sv.ussh.edu.vn'))))),
        // Danh sách email quản trị viên được phép đăng nhập Google với vai trò Admin
        'admin_emails' => array_filter(array_map('trim', explode(',', strtolower((string)($_ENV['GOOGLE_ADMIN_EMAILS'] ?? ''))))),
        // Tương thích ngược: tất cả domain/email hợp lệ
        'allowed_domains' => array_filter(array_map('trim', explode(',', strtolower((string)($_ENV['GOOGLE_STUDENT_DOMAINS'] ?? 'gm.uit.edu.vn,sv.ussh.edu.vn'))))),
        'prompt' => 'select_account',
    ],
];
