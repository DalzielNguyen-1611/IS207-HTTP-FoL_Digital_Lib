<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoomRepository;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;

class RoomBookingService
{
    private RoomRepository $roomRepository;

    public function __construct(?RoomRepository $roomRepository = null)
    {
        $this->roomRepository = $roomRepository ?? new RoomRepository();
    }

    public function createBooking(int $userId, string $date, int $shiftId): array
    {
        if ($userId <= 0 || $shiftId <= 0) {
            throw new DomainException('Thông tin tài khoản hoặc ca đọc không hợp lệ.');
        }

        $timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
        $bookingDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timeZone);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (
            $bookingDate === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $bookingDate->format('Y-m-d') !== $date
        ) {
            throw new DomainException('Ngày đặt chỗ không hợp lệ.');
        }

        $firstDate = (new DateTimeImmutable('today', $timeZone))->modify('next monday');
        $lastDate = $firstDate->modify('+4 days');
        if ($bookingDate < $firstDate || $bookingDate > $lastDate) {
            throw new DomainException('Chỉ có thể đặt chỗ trong lịch từ thứ Hai đến thứ Sáu tuần tới.');
        }

        return $this->roomRepository->createBooking(
            $userId,
            $date,
            $shiftId,
            new DateTimeImmutable('now', $timeZone)
        );
    }

    public function getBookings(int $userId): array
    {
        if ($userId <= 0) {
            throw new DomainException('Không xác định được tài khoản.');
        }

        return $this->roomRepository->getBookingsByUser($userId);
    }

    public function getSchedule(?string $weekStart = null): array
    {
        $timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
        if ($weekStart === null || $weekStart === '') {
            $startDate = (new DateTimeImmutable('today', $timeZone))->modify('next monday');
        } else {
            $startDate = DateTimeImmutable::createFromFormat('!Y-m-d', $weekStart, $timeZone);
            $dateErrors = DateTimeImmutable::getLastErrors();
            if (
                $startDate === false
                || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
                || $startDate->format('Y-m-d') !== $weekStart
                || $startDate->format('N') !== '1'
            ) {
                throw new DomainException('Tuần được chọn không hợp lệ.');
            }
        }

        return $this->roomRepository->getWeeklySchedule($startDate);
    }

    public function cancelBooking(int $userId, int $bookingId): void
    {
        if ($userId <= 0 || $bookingId <= 0) {
            throw new DomainException('Thông tin lượt đặt chỗ không hợp lệ.');
        }

        $this->roomRepository->cancelBooking(
            $userId,
            $bookingId,
            new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'))
        );
    }
}
