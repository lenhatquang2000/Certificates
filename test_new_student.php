<?php
// Test script để kiểm tra lỗi New Student

require_once 'vendor/autoload.php';

// Test 1: Kiểm tra thư mục ảnh nền
echo "=== Test 1: Kiểm tra thư mục ảnh nền ===\n";
$templatePath = 'public/assets/newDoctorTemplate';
if (is_dir($templatePath)) {
    echo "✓ Thư mục tồn tại: $templatePath\n";
    $files = scandir($templatePath);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'jpg' || pathinfo($file, PATHINFO_EXTENSION) === 'png') {
            echo "✓ Tìm thấy ảnh: $file\n";
            $fullPath = $templatePath . '/' . $file;
            if (file_exists($fullPath)) {
                echo "  - File tồn tại: $fullPath\n";
                $size = filesize($fullPath);
                echo "  - Kích thước: " . number_format($size) . " bytes\n";
            } else {
                echo "  ✗ File không tồn tại: $fullPath\n";
            }
        }
    }
} else {
    echo "✗ Thư mục không tồn tại: $templatePath\n";
}

// Test 2: Kiểm tra base64 encoding
echo "\n=== Test 2: Kiểm tra base64 encoding ===\n";
$testImage = 'public/assets/newDoctorTemplate/TÂN BÁC SĨ.jpg';
if (file_exists($testImage)) {
    echo "✓ Test image tồn tại: $testImage\n";
    $base64 = base64_encode(file_get_contents($testImage));
    echo "✓ Base64 encoding thành công, độ dài: " . strlen($base64) . " characters\n";
} else {
    echo "✗ Test image không tồn tại: $testImage\n";
}

// Test 3: Kiểm tra font files
echo "\n=== Test 3: Kiểm tra font files ===\n";
$fonts = [
    'public/fonts/UTM HelvetIns.ttf',
    'public/fonts/unicode.display.UVNNguyenDu.TTF',
    'public/fonts/timesbd_0.ttf',
    'public/fonts/times_0.ttf',
    'public/fonts/SVN-Agency FB.ttf'
];

foreach ($fonts as $font) {
    if (file_exists($font)) {
        echo "✓ Font tồn tại: $font\n";
    } else {
        echo "✗ Font không tồn tại: $font\n";
    }
}

echo "\n=== Test hoàn thành ===\n";
?>
