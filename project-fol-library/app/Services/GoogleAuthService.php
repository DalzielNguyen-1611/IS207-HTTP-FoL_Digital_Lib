<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use DomainException;

/**
 * Service Xử lý Xác thực Google OAuth 2.0 / OpenID Connect (Google Auth Service)
 * Tuân thủ chuẩn bảo mật Google: prompt=select_account, verify token, CSRF state
 */
class GoogleAuthService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private array $allowedDomains;
    private array $studentDomains;
    private array $adminEmails;
    private string $prompt;

    public function __construct(?array $config = null)
    {
        if ($config === null) {
            $configPath = dirname(__DIR__, 2) . '/config/auth.php';
            $config = file_exists($configPath) ? (require $configPath)['google'] ?? [] : [];
        }

        $this->clientId = (string)($config['client_id'] ?? '');
        $this->clientSecret = (string)($config['client_secret'] ?? '');
        $this->redirectUri = (string)($config['redirect_uri'] ?? 'http://localhost:8000/auth/google/callback');
        $this->allowedDomains = (array)($config['allowed_domains'] ?? []);
        $this->studentDomains = (array)($config['student_domains'] ?? ['gm.uit.edu.vn', 'sv.ussh.edu.vn']);
        $this->adminEmails = array_map('strtolower', (array)($config['admin_emails'] ?? []));
        $this->prompt = (string)($config['prompt'] ?? 'select_account');
    }

    /**
     * Kiểm tra xem cấu hình Google OAuth đã được điền Client ID và Secret chưa
     */
    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->clientSecret !== '';
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * Tạo đường dẫn chuyển hướng sang Google OAuth 2.0 (bắt buộc chọn tài khoản)
     */
    public function createAuthUrl(string $state): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => $this->prompt, // Yêu cầu Google hiển thị bảng chọn tài khoản
            'access_type' => 'online'
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Thực hiện HTTP request qua cURL với cơ chế ép IPv4 và tự động thử lại khi mạng chập chờn
     */
    private function executeHttpRequest(string $url, array $options, int $maxRetries = 2): array
    {
        $defaultOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Bắt buộc dùng IPv4 để tránh nghẽn/timeout IPv6 trên Windows/ISP
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        $mergedOptions = $options + $defaultOptions;

        $lastError = '';
        $lastHttpCode = 0;
        $lastResponse = false;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, $mergedOptions);

            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response !== false && $httpCode === 200) {
                return [
                    'success' => true,
                    'code' => $httpCode,
                    'body' => (string)$response
                ];
            }

            $lastError = $curlError ?: (string)$response;
            $lastHttpCode = $httpCode;
            $lastResponse = $response;

            // Nếu gặp lỗi mạng tạm thời (timeout, connection reset), nghỉ 400ms rồi thử lại
            if ($attempt < $maxRetries) {
                usleep(400000);
            }
        }

        return [
            'success' => false,
            'code' => $lastHttpCode,
            'error' => $lastError,
            'body' => (string)$lastResponse
        ];
    }

    /**
     * Đổi mã authorization code lấy token và xác minh ID Token từ Google API
     * @return array{sub: string, email: string, email_verified: bool, name: string, picture: string}
     */
    public function exchangeAndVerify(string $code): array
    {
        // 1. Gọi Google Token Endpoint để đổi Authorization Code
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $postData = http_build_query([
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code'
        ]);

        $tokenResult = $this->executeHttpRequest($tokenUrl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        if (!$tokenResult['success']) {
            $detail = $tokenResult['error'] !== '' ? $tokenResult['error'] : $tokenResult['body'];
            throw new RuntimeException("Lỗi kết nối trao đổi token với Google (HTTP {$tokenResult['code']}): {$detail}");
        }

        $tokenData = json_decode($tokenResult['body'], true);
        if (!is_array($tokenData) || empty($tokenData['access_token'])) {
            throw new RuntimeException('Phản hồi token từ Google không hợp lệ.');
        }

        $accessToken = (string)$tokenData['access_token'];
        $idToken = (string)($tokenData['id_token'] ?? '');

        // 2. Xác minh và lấy thông tin người dùng từ Google UserInfo Endpoint
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
        $userResult = $this->executeHttpRequest($userInfoUrl, [
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$accessToken}"],
        ]);

        if (!$userResult['success']) {
            $detail = $userResult['error'] !== '' ? $userResult['error'] : $userResult['body'];
            throw new RuntimeException("Không thể xác thực thông tin tài khoản người dùng từ Google (HTTP {$userResult['code']}): {$detail}");
        }

        $payload = json_decode($userResult['body'], true);
        if (!is_array($payload) || empty($payload['sub']) || empty($payload['email'])) {
            throw new RuntimeException('Dữ liệu hồ sơ người dùng trả về từ Google bị thiếu trường bắt buộc.');
        }

        if (($payload['email_verified'] ?? false) !== true) {
            throw new DomainException('Địa chỉ email của bạn chưa được Google xác minh.');
        }

        return [
            'sub' => (string)$payload['sub'],
            'email' => strtolower(trim((string)$payload['email'])),
            'email_verified' => true,
            'name' => (string)($payload['name'] ?? explode('@', (string)$payload['email'])[0]),
            'picture' => (string)($payload['picture'] ?? '/assets/images/logo.png'),
            'hd' => (string)($payload['hd'] ?? '')
        ];
    }

    /**
     * Kiểm tra tên miền email có thuộc danh sách sinh viên (@gm.uit.edu.vn, @sv.ussh.edu.vn) hay không
     */
    public function isStudentDomain(string $email): bool
    {
        if (empty($this->studentDomains)) {
            return false;
        }

        $parts = explode('@', strtolower(trim($email)));
        if (count($parts) !== 2 || $parts[0] === '') {
            return false;
        }

        $domain = $parts[1];
        $normalized = array_map('strtolower', $this->studentDomains);

        return in_array($domain, $normalized, true);
    }

    /**
     * Kiểm tra email có nằm trong danh sách Quản trị viên (Admin) được cấu hình hay không
     */
    public function isAdminEmail(string $email): bool
    {
        $normalizedEmail = strtolower(trim($email));
        return in_array($normalizedEmail, $this->adminEmails, true);
    }

    /**
     * Kiểm tra tính hợp lệ của email (sinh viên hoặc quản trị viên)
     */
    public function isAllowed(string $email): bool
    {
        return $this->isAdminEmail($email) || $this->isStudentDomain($email);
    }

    /**
     * Tương thích ngược: kiểm tra email được phép đăng nhập Google
     */
    public function isAllowedDomain(string $email): bool
    {
        return $this->isAllowed($email);
    }

    public function getStudentDomains(): array
    {
        return $this->studentDomains;
    }

    public function getAdminEmails(): array
    {
        return $this->adminEmails;
    }
}
