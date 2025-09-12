<?php
/**
 * File test để kiểm tra chức năng đọc Excel
 * Chạy file này để test trực tiếp mà không cần qua web server
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Exception;

$filePath = 'C:\Users\popix\Downloads\ids.xlsx';

echo "=== KIỂM TRA FILE EXCEL ===\n";
echo "Đường dẫn file: $filePath\n\n";

try {
    // Kiểm tra file có tồn tại không
    if (!file_exists($filePath)) {
        echo "❌ LỖI: File không tồn tại tại đường dẫn: $filePath\n";
        exit(1);
    }

    echo "✅ File tồn tại\n";
    echo "📊 Đang đọc file Excel...\n\n";

    // Đọc file Excel
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Lấy thông tin cơ bản
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "📋 Thông tin file:\n";
    echo "   - Dòng cao nhất: $highestRow\n";
    echo "   - Cột cao nhất: $highestColumn\n\n";
    
    // Đọc dữ liệu từ cột đầu tiên (cột A)
    $data = [];
    $rowCount = 0;
    
    echo "🔍 Đang đọc dữ liệu từ cột A...\n";
    
    for ($row = 1; $row <= $highestRow; $row++) {
        $cellValue = $worksheet->getCell('A' . $row)->getValue();
        
        // Chỉ đếm dòng có dữ liệu (không rỗng)
        if (!empty(trim($cellValue))) {
            $data[] = [
                'row' => $row,
                'value' => $cellValue
            ];
            $rowCount++;
        }
    }
    
    echo "\n📊 KẾT QUẢ:\n";
    echo "   - Tổng số dòng trong file: $highestRow\n";
    echo "   - Số dòng có dữ liệu: $rowCount\n";
    echo "   - Số mục dữ liệu: " . count($data) . "\n\n";
    
    if (count($data) > 0) {
        echo "📋 DANH SÁCH DỮ LIỆU:\n";
        echo "   " . str_repeat("-", 50) . "\n";
        echo "   | STT | Dòng | Giá trị\n";
        echo "   " . str_repeat("-", 50) . "\n";
        
        foreach ($data as $index => $item) {
            $stt = str_pad($index + 1, 3, ' ', STR_PAD_LEFT);
            $row = str_pad($item['row'], 4, ' ', STR_PAD_LEFT);
            $value = substr($item['value'], 0, 30); // Giới hạn độ dài hiển thị
            
            echo "   | $stt | $row | $value\n";
        }
        
        echo "   " . str_repeat("-", 50) . "\n";
    } else {
        echo "⚠️  Không có dữ liệu nào trong cột A\n";
    }
    
    echo "\n✅ Hoàn thành kiểm tra!\n";
    
} catch (Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . "\n";
    exit(1);
}
