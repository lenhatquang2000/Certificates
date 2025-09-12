<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Exception;

class ExcelController extends Controller
{
    /**
     * Kiểm tra file Excel và đếm số dòng
     */
    public function checkExcelFile()
    {
        $filePath = 'C:\Users\popix\Downloads\ids.xlsx';
        
        try {
            // Kiểm tra file có tồn tại không
            if (!file_exists($filePath)) {
                return view('excel.check-result', [
                    'error' => 'File không tồn tại tại đường dẫn: ' . $filePath,
                    'filePath' => $filePath,
                    'rowCount' => 0,
                    'data' => []
                ]);
            }

            // Đọc file Excel
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Lấy dữ liệu từ cột đầu tiên (cột A)
            $data = [];
            $rowCount = 0;
            
            // Đọc từ dòng 1 đến dòng cuối cùng có dữ liệu
            $highestRow = $worksheet->getHighestRow();
            
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
            
            return view('excel.check-result', [
                'filePath' => $filePath,
                'rowCount' => $rowCount,
                'data' => $data,
                'totalRows' => $highestRow,
                'error' => null
            ]);
            
        } catch (Exception $e) {
            return view('excel.check-result', [
                'error' => 'Lỗi khi đọc file Excel: ' . $e->getMessage(),
                'filePath' => $filePath,
                'rowCount' => 0,
                'data' => []
            ]);
        }
    }
    
    /**
     * API endpoint để trả về JSON
     */
    public function checkExcelFileApi()
    {
        $filePath = 'C:\Users\popix\Downloads\ids.xlsx';
        
        try {
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File không tồn tại tại đường dẫn: ' . $filePath,
                    'filePath' => $filePath,
                    'rowCount' => 0,
                    'data' => []
                ], 404);
            }

            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $data = [];
            $rowCount = 0;
            $highestRow = $worksheet->getHighestRow();
            
            for ($row = 1; $row <= $highestRow; $row++) {
                $cellValue = $worksheet->getCell('A' . $row)->getValue();
                
                if (!empty(trim($cellValue))) {
                    $data[] = [
                        'row' => $row,
                        'value' => $cellValue
                    ];
                    $rowCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'filePath' => $filePath,
                'rowCount' => $rowCount,
                'totalRows' => $highestRow,
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Lỗi khi đọc file Excel: ' . $e->getMessage(),
                'filePath' => $filePath,
                'rowCount' => 0,
                'data' => []
            ], 500);
        }
    }
}
