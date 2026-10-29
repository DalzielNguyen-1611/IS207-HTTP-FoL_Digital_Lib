<?php

declare(strict_types=1);

return [
    'google' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'] ?? getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:8000/auth/google/callback',
        // Danh sách domain email được phép đăng nhập
        'allowed_domains' => array_filter(array_map('trim', explode(',', (string)($_ENV['GOOGLE_ALLOWED_DOMAINS'] ?? 'gm.uit.edu.vn,uit.edu.vn,ussh.edu.vn,gmail.com')))),
        'prompt' => 'select_account',
    ],
];
