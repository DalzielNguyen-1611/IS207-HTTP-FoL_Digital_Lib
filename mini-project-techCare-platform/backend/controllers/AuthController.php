<?php
/**
 * Auth Controller
 * Xử lý đăng nhập, đăng ký và xác thực người dùng thật
 * Mật khẩu được kiểm tra bằng password_verify đối chiếu với password_hash (Bcrypt)
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    /**
     * Đăng nhập người dùng thật
     */
    public static function login() {
        $data = Response::getBody();
        $account = trim($data['account'] ?? ($data['username'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if (empty($account) || empty($password)) {
            Response::error('Vui lòng nhập tên đăng nhập (hoặc Số điện thoại / Email) và mật khẩu.', 400);
        }

        // Tìm người dùng trong CSDL hoặc Mock
        $user = User::findByLogin($account);

        if (!$user) {
            Response::error('Tài khoản không tồn tại trong hệ thống. Vui lòng kiểm tra lại.', 401);
        }

        $hash = $user['password_hash'] ?? ($user['password'] ?? '');
        $isPasswordCorrect = false;

        // Xác thực mật khẩu qua thuật toán băm chuẩn PHP Bcrypt
        if (!empty($hash) && password_verify($password, $hash)) {
            $isPasswordCorrect = true;
        } elseif ($hash === $password) {
            // Tự động nâng cấp mật khẩu cũ dạng thô sang hash Bcrypt
            $isPasswordCorrect = true;
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $db = Database::getConnection();
            if ($db) {
                try {
                    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $user['id']]);
                } catch (Exception $e) {}
            }
        }

        if (!$isPasswordCorrect) {
            Response::error('Mật khẩu không chính xác. Vui lòng kiểm tra lại!', 401);
        }

        if (isset($user['status']) && $user['status'] === 'banned') {
            Response::error('Tài khoản này đã bị khóa. Vui lòng liên hệ hotline 1800 6868.', 403);
        }

        // Tạo token JWT có thời hạn 120 phút và chữ ký bảo mật
        $tokenData = AuthHelper::generateToken($user, 120);

        Response::json([
            'success'       => true,
            'message'       => 'Đăng nhập thành công!',
            'token'         => $tokenData['token'],
            'token_type'    => $tokenData['token_type'],
            'expires_in'    => $tokenData['expires_in'],
            'expires_at'    => $tokenData['expires_at'],
            'expires_at_ts' => $tokenData['expires_at_ts'],
            'user'          => $tokenData['user']
        ]);
    }

    /**
     * Đăng ký tài khoản mới thật
     */
    public static function register() {
        $data = Response::getBody();
        $fullName = trim($data['full_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $email = trim($data['email'] ?? '');
        $username = trim($data['username'] ?? '');
        $password = (string)($data['password'] ?? '');
        $address = trim($data['address'] ?? '');

        if (empty($fullName)) {
            Response::error('Vui lòng nhập họ và tên của bạn.', 400);
        }
        if (empty($phone) && empty($username) && empty($email)) {
            Response::error('Vui lòng cung cấp số điện thoại hoặc tên đăng nhập.', 400);
        }
        if (empty($password)) {
            Response::error('Vui lòng nhập mật khẩu.', 400);
        }
        if (strlen($password) < 6) {
            Response::error('Mật khẩu phải có độ dài từ 6 ký tự trở lên.', 400);
        }

        // Tự sinh username hoặc email nếu người dùng chỉ nhập số điện thoại
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($username)) {
            $username = !empty($cleanPhone) ? 'user_' . $cleanPhone : 'user_' . time();
        }
        if (empty($email)) {
            $email = (!empty($cleanPhone) ? $cleanPhone : $username) . '@khachhang.techcare.vn';
        }

        // Kiểm tra trùng lặp tài khoản
        if (User::findByLogin($username) || (!empty($cleanPhone) && User::findByLogin($cleanPhone)) || User::findByLogin($email)) {
            Response::error('Số điện thoại, email hoặc tên đăng nhập này đã được sử dụng. Vui lòng đăng nhập hoặc đổi thông tin.', 409);
        }

        try {
            $newUser = User::create([
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'password' => $password, // Sẽ được User::create băm bằng password_hash(..., PASSWORD_BCRYPT)
                'full_name' => $fullName,
                'role' => 'customer'
            ]);

            $tokenData = AuthHelper::generateToken($newUser, 120);

            Response::json([
                'success'       => true,
                'message'       => 'Đăng ký tài khoản thành công!',
                'token'         => $tokenData['token'],
                'token_type'    => $tokenData['token_type'],
                'expires_in'    => $tokenData['expires_in'],
                'expires_at'    => $tokenData['expires_at'],
                'expires_at_ts' => $tokenData['expires_at_ts'],
                'user'          => $tokenData['user']
            ], 201);
        } catch (Exception $e) {
            Response::error('Không thể tạo tài khoản: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Lấy thông tin user hiện tại qua Token xác thực
     */
    public static function profile() {
        $payload = AuthHelper::requireAuth();
        $user = User::findById($payload['user_id']);
        if ($user) {
            unset($user['password_hash'], $user['password']);
        } else {
            $user = $payload;
        }

        Response::json([
            'success' => true,
            'user'    => $user
        ]);
    }

    /**
     * Kiểm tra tính hợp lệ và thời gian còn lại của phiên làm việc
     */
    public static function verify() {
        $payload = AuthHelper::requireAuth();
        $now = time();
        $exp = $payload['exp'] ?? ($now + 7200);
        $timeRemaining = max(0, $exp - $now);

        Response::json([
            'success'                => true,
            'message'                => 'Phiên làm việc hợp lệ.',
            'user'                   => $payload,
            'expires_at'             => date('c', $exp),
            'expires_at_ts'          => $exp,
            'time_remaining_seconds' => $timeRemaining,
            'time_remaining_minutes' => round($timeRemaining / 60, 1)
        ]);
    }
}
