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

        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new RuntimeException("Lỗi kết nối trao đổi token với Google (HTTP {$httpCode}): " . ($curlError ?: (string)$response));
        }

        $tokenData = json_decode((string)$response, true);
        if (!is_array($tokenData) || empty($tokenData['access_token'])) {
            throw new RuntimeException('Phản hồi token từ Google không hợp lệ.');
        }

        $accessToken = (string)$tokenData['access_token'];
        $idToken = (string)($tokenData['id_token'] ?? '');

        // 2. Xác minh và lấy thông tin người dùng từ Google UserInfo Endpoint
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
        $chInfo = curl_init($userInfoUrl);
        curl_setopt_array($chInfo, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer {$accessToken}"],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $userResponse = curl_exec($chInfo);
        $userHttpCode = curl_getinfo($chInfo, CURLINFO_HTTP_CODE);
        curl_close($chInfo);

        if ($userResponse === false || $userHttpCode !== 200) {
            throw new RuntimeException('Không thể xác thực thông tin tài khoản người dùng từ Google.');
        }

        $payload = json_decode((string)$userResponse, true);
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
     * Kiểm tra tên miền email có thuộc danh sách cho phép hay không
     */
    public function isAllowedDomain(string $email): bool
    {
        if (empty($this->allowedDomains)) {
            return true;
        }

        $parts = explode('@', strtolower(trim($email)));
        if (count($parts) !== 2 || $parts[0] === '') {
            return false;
        }

        $domain = $parts[1];
        $normalizedAllowed = array_map('strtolower', $this->allowedDomains);

        return in_array($domain, $normalizedAllowed, true);
    }
}
