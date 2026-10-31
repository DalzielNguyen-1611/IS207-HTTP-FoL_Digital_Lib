<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Services\ImageOptimizerService;
use InvalidArgumentException;

/**
 * Bộ kiểm thử đơn vị cho Dịch vụ Tối ưu hóa & Nén ảnh (ImageOptimizerServiceTest)
 */
class ImageOptimizerServiceTest
{
    private ImageOptimizerService $service;
    private string $tempDir;

    public function __construct()
    {
        $this->service = new ImageOptimizerService();
        $this->tempDir = sys_get_temp_dir() . '/fol_img_test_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    public function __destruct()
    {
        // Dọn dẹp thư mục tạm
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            if ($files) {
                foreach ($files as $f) {
                    @unlink($f);
                }
            }
            @rmdir($this->tempDir);
        }
    }

    public function runAll(): array
    {
        $results = [];

        // 1. Kiểm tra phát hiện file không tồn tại
        $results['rejects_non_existent_file'] = $this->assert(
            $this->testThrows(fn() => $this->service->optimize('/path/to/invalid/file.jpg', $this->tempDir . '/out.webp'), InvalidArgumentException::class),
            'Phải ném ngoại lệ khi file nguồn không tồn tại'
        );

        // 2. Tạo ảnh mẫu giả lập ảnh chụp điện thoại (kích thước lớn: 2400 x 1800 px)
        $largeImgPath = $this->tempDir . '/sample_phone_photo.jpg';
        $largeCanvas = imagecreatetruecolor(2400, 1800);
        $red = imagecolorallocate($largeCanvas, 200, 50, 50);
        $blue = imagecolorallocate($largeCanvas, 50, 100, 200);
        imagefilledrectangle($largeCanvas, 0, 0, 2400, 1800, $red);
        imagefilledellipse($largeCanvas, 1200, 900, 800, 600, $blue);
        imagejpeg($largeCanvas, $largeImgPath, 100); // Lưu chất lượng 100% để file nặng
        imagedestroy($largeCanvas);

        $largeSize = (int)filesize($largeImgPath);

        // 3. Kiểm thử nén và thu nhỏ ảnh
        $targetWebp = $this->tempDir . '/optimized_student_card.webp';
        $stats = $this->service->optimize(
            sourcePath: $largeImgPath,
            targetPath: $targetWebp,
            maxWidth: 1000,
            maxHeight: 1000,
            quality: 80
        );

        $results['optimizes_successfully'] = $this->assert(
            $stats['success'] === true && file_exists($targetWebp),
            'Tối ưu hóa và tạo file WebP thành công'
        );

        $results['resizes_proportional_dimensions'] = $this->assert(
            $stats['width'] === 1000 && $stats['height'] === 750,
            "Thu nhỏ đúng tỷ lệ (2400x1800 -> 1000x750, thực tế: {$stats['width']}x{$stats['height']})"
        );

        $results['reduces_file_size_significantly'] = $this->assert(
            $stats['optimized_size'] < $largeSize && $stats['saved_percent'] > 50,
            "Dung lượng giảm đáng kể (>50%, thực tế giảm {$stats['saved_percent']}%, từ {$stats['original_size']} B xuống {$stats['optimized_size']} B)"
        );

        // 4. Kiểm thử với ảnh PNG có kênh trong suốt (Alpha channel)
        $pngPath = $this->tempDir . '/sample_transparent.png';
        $pngCanvas = imagecreatetruecolor(400, 400);
        imagealphablending($pngCanvas, false);
        imagesavealpha($pngCanvas, true);
        $trans = imagecolorallocatealpha($pngCanvas, 0, 0, 0, 127);
        imagefilledrectangle($pngCanvas, 0, 0, 400, 400, $trans);
        $green = imagecolorallocate($pngCanvas, 0, 200, 50);
        imagefilledellipse($pngCanvas, 200, 200, 200, 200, $green);
        imagepng($pngCanvas, $pngPath);
        imagedestroy($pngCanvas);

        $targetPngWebp = $this->tempDir . '/optimized_png.webp';
        $pngStats = $this->service->optimize(
            sourcePath: $pngPath,
            targetPath: $targetPngWebp,
            maxWidth: 300,
            maxHeight: 300,
            quality: 80
        );

        $results['handles_transparent_png'] = $this->assert(
            $pngStats['success'] === true && $pngStats['width'] === 300 && $pngStats['height'] === 300,
            'Xử lý mượt mà ảnh PNG trong suốt và chuyển đổi sang WebP 300x300'
        );

        return $results;
    }

    private function assert(bool $condition, string $message): array
    {
        return [
            'status' => $condition ? 'PASS' : 'FAIL',
            'message' => $message,
        ];
    }

    private function testThrows(callable $fn, string $expectedClass): bool
    {
        try {
            $fn();
            return false;
        } catch (\Throwable $e) {
            return $e instanceof $expectedClass;
        }
    }
}

// Nếu gọi trực tiếp từ dòng lệnh
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "=== CHẠY BỘ KIỂM THỬ: ImageOptimizerServiceTest ===" . PHP_EOL . PHP_EOL;
    $test = new ImageOptimizerServiceTest();
    $results = $test->runAll();

    $passed = 0;
    $total = count($results);

    foreach ($results as $name => $res) {
        $status = $res['status'];
        if ($status === 'PASS') {
            $passed++;
        }
        printf("[%s] %-35s : %s" . PHP_EOL, $status, $name, $res['message']);
    }

    echo PHP_EOL . "----------------------------------------------------" . PHP_EOL;
    echo "Tổng số test cases: {$total} | Đạt: {$passed} | Thất bại: " . ($total - $passed) . PHP_EOL;
    echo "----------------------------------------------------" . PHP_EOL;

    exit($passed === $total ? 0 : 1);
}
