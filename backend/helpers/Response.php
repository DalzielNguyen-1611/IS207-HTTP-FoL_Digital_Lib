<?php
/**
 * Response Helper
 * Quản lý định dạng phản hồi JSON, HTTP Status Code và cấu hình CORS
 */

class Response {
    /**
     * Cấu hình Header CORS cho toàn bộ API
     */
    public static function setCorsHeaders() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Auth-Token");
        header("Content-Type: application/json; charset=UTF-8");

        // Xử lý Preflight request của trình duyệt
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Phản hồi lỗi chưa xác thực (401 Unauthorized / Expired)
     */
    public static function unauthorized($message = 'Vui lòng đăng nhập để tiếp tục.', $expired = false) {
        self::json([
            'success' => false,
            'code'    => $expired ? 'SESSION_EXPIRED' : 'UNAUTHORIZED',
            'expired' => $expired,
            'message' => $message
        ], 401);
    }

    /**
     * Phản hồi lỗi bị cấm quyền truy cập (403 Forbidden)
     */
    public static function forbidden($message = 'Bạn không có quyền truy cập chức năng này.') {
        self::json([
            'success' => false,
            'code'    => 'FORBIDDEN',
            'message' => $message
        ], 403);
    }

    /**
     * Trả về phản hồi JSON thuần
     */
    public static function json($data, $statusCode = 200) {
        self::setCorsHeaders();
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Phản hồi thành công chuẩn
     */
    public static function success($message, $data = null, $statusCode = 200) {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        if ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response, $statusCode);
    }

    /**
     * Phản hồi lỗi chuẩn
     */
    public static function error($message, $statusCode = 400, $errors = null) {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        self::json($response, $statusCode);
    }

    /**
     * Lấy dữ liệu JSON từ request body
     */
    public static function getBody() {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return $_POST;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : $_POST;
    }
}
