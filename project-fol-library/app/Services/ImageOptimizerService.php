<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

/**
 * Dịch vụ Tối ưu hóa, Thu nhỏ và Nén ảnh tự động (Image Optimizer Service)
 * - Tự động thu nhỏ (Resize) theo tỷ lệ khung hình
 * - Tự động xoay ảnh theo EXIF Orientation từ điện thoại thông minh
 * - Chuyển đổi định dạng sang WebP (giảm 70-90% dung lượng)
 * - Nén chất lượng hợp lý (80% Quality Compression)
 */
class ImageOptimizerService
{
    /**
     * Tối ưu hóa và chuyển đổi file ảnh sang WebP
     *
     * @param string $sourcePath Đường dẫn file ảnh gốc
     * @param string $targetPath Đường dẫn lưu file đích (.webp)
     * @param int $maxWidth Chiều rộng tối đa (pixel)
     * @param int $maxHeight Chiều cao tối đa (pixel)
     * @param int $quality Mức chất lượng nén (1 - 100, mặc định 80)
     * @return array<string, mixed> Thống kê nén ảnh
     */
    public function optimize(
        string $sourcePath,
        string $targetPath,
        int $maxWidth = 1000,
        int $maxHeight = 1000,
        int $quality = 80
    ): array {
        if (!file_exists($sourcePath) || !is_readable($sourcePath)) {
            throw new InvalidArgumentException("File ảnh nguồn không tồn tại hoặc không thể đọc: {$sourcePath}");
        }

        if (!extension_loaded('gd')) {
            throw new RuntimeException('Thư viện PHP GD chưa được kích hoạt trên máy chủ.');
        }

        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new InvalidArgumentException("Định dạng file không phải là ảnh hợp lệ: {$sourcePath}");
        }

        [$origWidth, $origHeight, $imageType] = $imageInfo;
        $originalSize = (int)filesize($sourcePath);

        // 1. Nạp ảnh từ bộ nhớ theo định dạng gốc
        $srcImage = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            default => null,
        };

        if (!$srcImage) {
            throw new RuntimeException("Không thể giải mã ảnh từ định dạng MIME: {$imageInfo['mime']}");
        }

        // 2. Tự động xoay ảnh theo EXIF Orientation (chống lật ngang khi chụp bằng điện thoại)
        if ($imageType === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                $srcImage = match ((int)$exif['Orientation']) {
                    3 => imagerotate($srcImage, 180, 0),
                    6 => imagerotate($srcImage, -90, 0),
                    8 => imagerotate($srcImage, 90, 0),
                    default => $srcImage,
                };
                if (in_array((int)$exif['Orientation'], [6, 8], true)) {
                    // Đảo chiều rộng và cao sau khi xoay 90 độ
                    [$origWidth, $origHeight] = [$origHeight, $origWidth];
                }
            }
        }

        // 3. Tính toán kích thước thu nhỏ (Proportional Resizing)
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight, 1.0);
        $newWidth = (int)max(1, round($origWidth * $ratio));
        $newHeight = (int)max(1, round($origHeight * $ratio));

        // 4. Tạo khung canvas đích và xử lý kênh trong suốt (Alpha channel)
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);
        if (!$targetImage) {
            imagedestroy($srcImage);
            throw new RuntimeException('Không thể khởi tạo vùng nhớ canvas hình ảnh.');
        }

        // Giữ độ trong suốt đối với ảnh PNG / WebP
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        if ($transparent !== false) {
            imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // 5. Thu phóng chất lượng cao (Resampling)
        imagecopyresampled(
            $targetImage,
            $srcImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $origWidth,
            $origHeight
        );

        // 6. Đảm bảo thư mục đích tồn tại
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            imagedestroy($srcImage);
            imagedestroy($targetImage);
            throw new RuntimeException("Không thể tạo thư mục lưu trữ ảnh: {$targetDir}");
        }

        // 7. Chuyển đổi và nén sang chuẩn WebP
        $success = imagewebp($targetImage, $targetPath, max(1, min(100, $quality)));

        // Giải phóng tài nguyên RAM ngay sau khi nén
        imagedestroy($srcImage);
        imagedestroy($targetImage);

        if (!$success || !file_exists($targetPath)) {
            throw new RuntimeException("Lỗi ghi file ảnh WebP ra ổ đĩa: {$targetPath}");
        }

        $optimizedSize = (int)filesize($targetPath);
        $savedBytes = max(0, $originalSize - $optimizedSize);
        $savedPercent = $originalSize > 0 ? round(($savedBytes / $originalSize) * 100, 1) : 0.0;

        return [
            'success' => true,
            'source_path' => $sourcePath,
            'target_path' => $targetPath,
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'saved_bytes' => $savedBytes,
            'saved_percent' => $savedPercent,
            'original_width' => $origWidth,
            'original_height' => $origHeight,
            'width' => $newWidth,
            'height' => $newHeight,
            'quality' => $quality,
        ];
    }

    /**
     * Tối ưu hóa file tải lên từ $_FILES
     *
     * @param array<string, mixed> $file Mảng $_FILES['key']
     * @param string $subDir Thư mục con trong storage/uploads/ (ví dụ: 'student_cards', 'books')
     * @param string $prefix Tiền tố tên file (ví dụ: 'the_sv_22520123')
     * @param int $maxWidth Chiều rộng tối đa
     * @param int $maxHeight Chiều cao tối đa
     * @param int $quality Chất lượng WebP
     * @return array<string, mixed>
     */
    public function optimizeUploadedFile(
        array $file,
        string $subDir,
        string $prefix = 'img',
        int $maxWidth = 1000,
        int $maxHeight = 1000,
        int $quality = 80
    ): array {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('File tải lên không hợp lệ hoặc bị lỗi mã truyền tải.');
        }

        $tmpPath = (string)$file['tmp_name'];
        if (!is_uploaded_file($tmpPath) && !file_exists($tmpPath)) {
            throw new InvalidArgumentException('File nguồn không phải là file upload hợp lệ.');
        }

        // Tạo tên file ngẫu nhiên an toàn chống đoán tên hoặc trùng lặp
        $cleanPrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix) ?: 'img';
        $uniqueSuffix = bin2hex(random_bytes(6));
        $filename = "{$cleanPrefix}_{$uniqueSuffix}.webp";

        $storageRoot = dirname(__DIR__, 2) . '/storage/uploads';
        $cleanSubDir = trim($subDir, '/\\');
        $targetPath = "{$storageRoot}/{$cleanSubDir}/{$filename}";

        $stats = $this->optimize($tmpPath, $targetPath, $maxWidth, $maxHeight, $quality);
        $stats['relative_url'] = "/uploads/{$cleanSubDir}/{$filename}";
        $stats['filename'] = $filename;

        return $stats;
    }
}
