<?php
/**
 * AuthHelper - Bộ Trợ Giúp Xác Thực & Phân Quyền (RBAC / Token Expiration)
 * Tầng Application / Middleware
 * 
 * - Chuẩn Token: JWT (JSON Web Token) ký bằng thuật toán HMAC-SHA256
 * - Hạn dùng phiên: Mặc định 120 phút (7200 giây)
 * - Tự động phát hiện phiên hết hạn và chống giả mạo quyền qua DevTools
 */

require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthHelper {
    /**
     * Khóa bí mật máy chủ dùng để ký chữ ký HMAC-SHA256
     */
    private static $secretKey = 'TechCare_Platform_2026_@Secure_JWT_Secret_Key_#PROD';

    /**
     * Thời gian sống mặc định của phiên: 120 phút (2 giờ)
     */
    const DEFAULT_EXPIRATION_MINUTES = 120;

    /**
     * Mã hóa Base64URL chuẩn RFC 7515
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Giải mã Base64URL chuẩn RFC 7515
     */
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4 === 0 ? strlen($data) : strlen($data) + 4 - strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Sinh Token JWT có thời hạn và chữ ký điện tử
     * 
     * @param array $user Thông tin người dùng từ CSDL
     * @param int $durationMinutes Số phút hiệu lực của phiên làm việc
     * @return array Dữ liệu token và thông tin phiên
     */
    public static function generateToken($user, $durationMinutes = self::DEFAULT_EXPIRATION_MINUTES) {
        $now = time();
        $exp = $now + ($durationMinutes * 60);

        // Header JWT chuẩn
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        // Nếu là KTV, lấy mã technician_id (vd: TECH-204)
        $techId = null;
        $techCode = null;
        if (($user['role'] ?? '') === 'technician') {
            $db = Database::getConnection();
            if ($db) {
                try {
                    $stmt = $db->prepare("SELECT id, code FROM technicians WHERE user_id = ? LIMIT 1");
                    $stmt->execute([$user['id']]);
                    $tech = $stmt->fetch();
                    if ($tech) {
                        $techId = $tech['id'];
                        $techCode = $tech['code'];
                    }
                } catch (Exception $e) {
                    // ignore
                }
            }
        }

        // Payload dữ liệu
        $payload = [
            'iss'            => 'techcare-platform',
            'user_id'        => $user['id'],
            'username'       => $user['username'],
            'full_name'      => $user['full_name'],
            'email'          => $user['email'],
            'phone'          => $user['phone'] ?? '',
            'role'           => $user['role'], // admin, dispatcher, technician, customer
            'technician_id'  => $techId,
            'technician_code'=> $techCode,
            'iat'            => $now,
            'exp'            => $exp
        ];

        $encodedHeader = self::base64UrlEncode(json_encode($header));
        $encodedPayload = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", self::$secretKey, true);
        $encodedSignature = self::base64UrlEncode($signature);

        $jwt = "$encodedHeader.$encodedPayload.$encodedSignature";

        return [
            'token'          => $jwt,
            'token_type'     => 'Bearer',
            'expires_in'     => $durationMinutes * 60,
            'expires_at'     => date('c', $exp),
            'expires_at_ts'  => $exp,
            'user'           => [
                'id'              => $user['id'],
                'username'        => $user['username'],
                'full_name'       => $user['full_name'],
                'email'           => $user['email'],
                'phone'           => $user['phone'] ?? '',
                'role'            => $user['role'],
                'technician_id'   => $techId,
                'technician_code' => $techCode
            ]
        ];
    }

    /**
     * Lấy token chuỗi từ Request HTTP
     */
    public static function getTokenFromRequest() {
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        // 1. Kiểm tra Header Authorization
        $authHeader = null;
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } else {
            foreach ($headers as $key => $val) {
                if (strtolower($key) === 'authorization') {
                    $authHeader = $val;
                    break;
                }
            }
        }

        if ($authHeader) {
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return trim($matches[1]);
            }
            return trim($authHeader);
        }

        // 2. Kiểm tra Header tùy chỉnh X-Auth-Token
        if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            return trim($_SERVER['HTTP_X_AUTH_TOKEN']);
        }
        foreach ($headers as $key => $val) {
            if (strtolower($key) === 'x-auth-token') {
                return trim($val);
            }
        }

        // 3. Fallback: Query Parameter hoặc Body
        if (!empty($_GET['token'])) {
            return trim($_GET['token']);
        }
        if (!empty($_POST['token'])) {
            return trim($_POST['token']);
        }

        return null;
    }

    /**
     * Xác thực Token và giải mã Payload
     * 
     * @param string $token
     * @return array Kết quả xác thực
     */
    public static function verifyToken($token) {
        if (empty($token)) {
            return [
                'valid'   => false,
                'error'   => 'Không tìm thấy token xác thực.',
                'expired' => false
            ];
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return [
                'valid'   => false,
                'error'   => 'Định dạng token không hợp lệ.',
                'expired' => false
            ];
        }

        list($encodedHeader, $encodedPayload, $encodedSignature) = $parts;

        // 1. Kiểm tra chữ ký HMAC-SHA256
        $expectedSignature = self::base64UrlEncode(hash_hmac('sha256', "$encodedHeader.$encodedPayload", self::$secretKey, true));
        if (!hash_equals($expectedSignature, $encodedSignature)) {
            return [
                'valid'   => false,
                'error'   => 'Chữ ký bảo mật không khớp. Token đã bị chỉnh sửa hoặc giả mạo!',
                'expired' => false
            ];
        }

        // 2. Giải mã Payload
        $payloadJson = self::base64UrlDecode($encodedPayload);
        $payload = json_decode($payloadJson, true);

        if (!is_array($payload)) {
            return [
                'valid'   => false,
                'error'   => 'Dữ liệu trong token không hợp lệ.',
                'expired' => false
            ];
        }

        // 3. Kiểm tra hạn phiên (Expiration)
        $now = time();
        if (isset($payload['exp']) && $payload['exp'] < $now) {
            return [
                'valid'        => false,
                'error'        => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.',
                'expired'      => true,
                'expired_at'   => date('c', $payload['exp']),
                'payload'      => $payload
            ];
        }

        // 4. Kiểm tra tài khoản trong CSDL (đề phòng bị khóa sau khi đã đăng nhập)
        $db = Database::getConnection();
        if ($db && !empty($payload['user_id'])) {
            try {
                $stmt = $db->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$payload['user_id']]);
                $userDb = $stmt->fetch();
                if ($userDb && $userDb['status'] === 'banned') {
                    return [
                        'valid'   => false,
                        'error'   => 'Tài khoản này đã bị khóa khỏi hệ thống.',
                        'expired' => false
                    ];
                }
            } catch (Exception $e) {
                // Database offline fallback
            }
        }

        return [
            'valid'   => true,
            'payload' => $payload
        ];
    }

    /**
     * Bắt buộc phải đăng nhập (Authentication Guard)
     * Trả về payload người dùng nếu hợp lệ, tự động ngắt và trả về 401 nếu không hợp lệ
     * 
     * @return array Payload của người dùng đã xác thực
     */
    public static function requireAuth() {
        $token = self::getTokenFromRequest();
        $result = self::verifyToken($token);

        if (!$result['valid']) {
            Response::unauthorized($result['error'], !empty($result['expired']));
        }

        return $result['payload'];
    }

    /**
     * Bắt buộc phải có vai trò hợp lệ (Role-Based Access Control - RBAC)
     * 
     * @param string|array $allowedRoles Danh sách vai trò được phép (vd: ['dispatcher', 'admin'])
     * @return array Payload của người dùng đã xác thực
     */
    public static function requireRole($allowedRoles) {
        $user = self::requireAuth();

        $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
        // 'admin' luôn có quyền cao nhất truy cập mọi tài nguyên
        if (!in_array('admin', $roles)) {
            $roles[] = 'admin';
        }

        $userRole = $user['role'] ?? '';
        if (!in_array($userRole, $roles)) {
            $roleNames = [
                'admin'      => 'Quản Trị Viên',
                'dispatcher' => 'Điều Phối Viên',
                'technician' => 'Kỹ Thuật Viên',
                'customer'   => 'Khách Hàng'
            ];
            $allowedStr = implode(', ', array_map(function($r) use ($roleNames) {
                return $roleNames[$r] ?? $r;
            }, $roles));
            $currentStr = $roleNames[$userRole] ?? $userRole;

            Response::forbidden("Bạn không có quyền thực hiện chức năng này. Quyền tài khoản hiện tại: [$currentStr]. Yêu cầu một trong các quyền: [$allowedStr].");
        }

        return $user;
    }
}
