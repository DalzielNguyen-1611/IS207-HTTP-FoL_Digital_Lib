<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Services\RoomBookingService;
use DomainException;
use JsonException;
use Throwable;

class RoomBookingController
{
    private RoomBookingService $bookingService;

    public function __construct(?RoomBookingService $bookingService = null)
    {
        $this->bookingService = $bookingService ?? new RoomBookingService();
    }

    public function index(): void
    {
        try {
            $this->respond([
                'success' => true,
                'schedule' => $this->bookingService->getSchedule(
                    isset($_GET['week']) ? (string)$_GET['week'] : null
                ),
                'bookings' => $this->bookingService->getBookings(
                    Session::getUserId() ?? 0
                ),
            ]);
        } catch (DomainException $exception) {
            $this->respond([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            $this->serverError($exception);
        }
    }

    public function create(): void
    {
        if (!$this->hasValidCsrfToken()) {
            $this->respond([
                'success' => false,
                'error' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
            ], 403);
            return;
        }

        $userId = Session::getUserId() ?? 0;

        // 1. Điều tiết lưu lượng (Rate Limiting: tối đa 10 lượt đặt / phút / tài khoản)
        $rateKey = "booking:user:{$userId}";
        if (\App\Core\RateLimiter::tooManyAttempts($rateKey, 10)) {
            $wait = \App\Core\RateLimiter::availableIn($rateKey);
            $this->respond([
                'success' => false,
                'error' => "Bạn thao tác đặt chỗ quá nhanh. Vui lòng chờ {$wait} giây rồi thử lại.",
            ], 429);
            return;
        }
        \App\Core\RateLimiter::hit($rateKey, 60);

        try {
            $data = $this->readJsonBody();

            // 2. Xử lý Idempotency Key (Chống gửi trùng lặp - Duplicate Submission)
            $idempotencyKey = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? ($data['idempotency_key'] ?? null);
            if (is_string($idempotencyKey) && trim($idempotencyKey) !== '') {
                $trimmedKey = trim($idempotencyKey);
                $cached = Session::get("idempotency_booking_{$userId}_{$trimmedKey}");
                if (is_array($cached)) {
                    $this->respond($cached, 200);
                    return;
                }
            }

            $date = $data['date'] ?? null;
            $shiftId = filter_var($data['shift_id'] ?? null, FILTER_VALIDATE_INT);
            if (!is_string($date) || $shiftId === false || $shiftId === null) {
                $this->respond([
                    'success' => false,
                    'error' => 'Vui lòng chọn ngày và ca đọc hợp lệ.',
                ], 422);
                return;
            }

            $booking = $this->bookingService->createBooking(
                $userId,
                $date,
                (int)$shiftId
            );

            $responseBody = [
                'success' => true,
                'message' => 'Đặt chỗ thành công.',
                'booking' => $booking,
            ];

            // Lưu cache kết quả theo Idempotency Key nếu có
            if (!empty($trimmedKey)) {
                Session::set("idempotency_booking_{$userId}_{$trimmedKey}", $responseBody);
            }

            $this->respond($responseBody, 201);
        } catch (DomainException $exception) {
            $this->respond([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 422);
        } catch (JsonException $exception) {
            $this->respond([
                'success' => false,
                'error' => 'Dữ liệu gửi lên không phải JSON hợp lệ.',
            ], 400);
        } catch (Throwable $exception) {
            $this->serverError($exception);
        }
    }

    public function cancel(): void
    {
        if (!$this->hasValidCsrfToken()) {
            $this->respond([
                'success' => false,
                'error' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
            ], 403);
            return;
        }

        try {
            $data = $this->readJsonBody();
            $bookingId = filter_var($data['booking_id'] ?? null, FILTER_VALIDATE_INT);
            if ($bookingId === false || $bookingId === null || $bookingId <= 0) {
                $this->respond([
                    'success' => false,
                    'error' => 'Mã lượt đặt chỗ không hợp lệ.',
                ], 422);
                return;
            }

            $this->bookingService->cancelBooking(
                Session::getUserId() ?? 0,
                (int)$bookingId
            );
            $this->respond([
                'success' => true,
                'message' => 'Đã hủy lượt đặt chỗ.',
            ]);
        } catch (DomainException $exception) {
            $this->respond([
                'success' => false,
                'error' => $exception->getMessage(),
            ], 422);
        } catch (JsonException $exception) {
            $this->respond([
                'success' => false,
                'error' => 'Dữ liệu gửi lên không phải JSON hợp lệ.',
            ], 400);
        } catch (Throwable $exception) {
            $this->serverError($exception);
        }
    }

    private function hasValidCsrfToken(): bool
    {
        $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!is_string($requestToken) || $requestToken === '') {
            $raw = file_get_contents('php://input');
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                $requestToken = $decoded['csrf_token'] ?? null;
            }
        }

        if (is_string($requestToken) && $requestToken !== '' && Session::validateCsrfToken($requestToken)) {
            return true;
        }

        $sessionToken = Session::get('csrf_token');
        return is_string($sessionToken)
            && is_string($requestToken)
            && $sessionToken !== ''
            && hash_equals($sessionToken, $requestToken);
    }

    /**
     * @throws JsonException
     */
    private function readJsonBody(): array
    {
        $body = file_get_contents('php://input');
        if (!is_string($body) || trim($body) === '') {
            return [];
        }

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        return is_array($data) ? $data : [];
    }

    private function respond(array $body, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function serverError(Throwable $exception): void
    {
        error_log('Room booking request failed: ' . $exception->getMessage());
        $this->respond([
            'success' => false,
            'error' => 'Không thể xử lý yêu cầu đặt chỗ lúc này. Vui lòng thử lại sau.',
        ], 500);
    }
}
