<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use Database\Seeders\RbacSeeder;
use Database\Seeders\OperationalSeeder;
use Database\Seeders\BookSeeder;

echo "=================================================================\n";
echo " FoL Digital Library - KHOI TAO DU LIEU CO SO DU LIEU (SEEDER)\n";
echo "=================================================================\n";

echo "1. Dang khoi tao Vai tro, Quyen han & Tai khoan mau (RBAC)...\n";
RbacSeeder::run();
echo "   -> Thanh cong! Tai khoan admin@fol.uit.edu.vn & librarian@fol.uit.edu.vn da san sang.\n\n";

echo "2. Dang khoi tao Co so vat chat Phong doc, Ca doc, Cho ngoi...\n";
OperationalSeeder::run(null, true);
echo "   -> Thanh cong! 40 cho ngoi & 3 ca doc da duoc nap.\n\n";

$withBooks = in_array('--with-books', $argv, true);
if ($withBooks) {
    echo "3. Dang nap danh muc sach mau (Book Seeder)...\n";
    BookSeeder::run();
    echo "   -> Thanh cong! Kho dau sach & cuon sach da duoc nap.\n\n";
} else {
    echo "3. Bo qua nap sach (Them tham so --with-books neu muon nap sach mau).\n\n";
}

echo "=================================================================\n";
echo " HOAN TAT KHOI TAO! BAN CO THE DANG NHAP NGAY TAI http://localhost:8000\n";
echo " - Admin:     admin@fol.uit.edu.vn     | Mat khau: Password@123\n";
echo " - Can bo:    librarian@fol.uit.edu.vn | Mat khau: Password@123\n";
echo " - Sinh vien: student@gm.uit.edu.vn    | Mat khau: Password@123\n";
echo "=================================================================\n";
