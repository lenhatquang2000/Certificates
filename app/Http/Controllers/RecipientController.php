<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelPdf\Facades\Pdf;
use setasign\Fpdi\Fpdi;
use Symfony\Component\Process\Exception\ProcessFailedException;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Imagick;
use Barryvdh\Snappy\Facades\SnappyImage;
class RecipientController extends Controller
{
    /**
     * Tại sao vào đây bị vòng lặp gọi route liên tục?
     *
     * Nguyên nhân phổ biến nhất là trong quá trình xử lý lỗi (catch), bạn dùng `return back()->withErrors(...)`.
     * Nếu route này (previewPdf) là GET và không có referer hợp lệ, hoặc chính nó là trang trước đó,
     * thì `back()` sẽ redirect lại chính route này, gây vòng lặp vô hạn.
     *
     * Để tránh vòng lặp, KHÔNG dùng `back()` trong các route chỉ dùng để xuất file/pdf hoặc API.
     * Thay vào đó, trả về response lỗi rõ ràng (ví dụ: abort(500) hoặc trả về view lỗi).
     */
    public function previewPdf()
    {
        // Dữ liệu mẫu đúng theo yêu cầu đề bài
        $data = [
            'recipient_name' => 'NGUYỄN VĂN A',
            'award_title' => 'Sinh viên Xuất sắc năm học',
            'award_title_english' => 'Excellent Student of The Class, Gcademic Year 2024 – 2025',
            'program' => 'TÀI CHÍNH - NGÂN HÀNG KHÓA 17/2024',
            'program_english' => 'Finance - Banking K17/2024',
            'issue_day' => 18,
            'issue_month' => 12,
            'issue_year' => 2024,
            'location' => 'Hậu Giang',
            'decision_prefix' => '245/QĐ-DHVTTU', // Để trống để auto sinh .../QĐ-ĐHVTT
            'decision_prefix_english' => '245/QD-DHVTTU', // Để trống để auto sinh .../QĐ-ĐHVTT
            'rector_name' => 'Dương Đăng Khoa',
            // Các trường này sẽ được processData tự động sinh ra:
            // 'vietnamese_date', 'decision_number', 'english_date'
            'position_adjustments' => [
                'recipient_name_top' => '0',
                'program_top' => '0',
                'award_title_top' => '0',
                'award_title_english_top' => '0',
                'program_english_top' => '0',
                'vietnamese_date_top' => '0',
                'decision_number_top' => '0',
                'rector_name_top' => '0',
                'english_date_top' => '0',
            ],
        ];

        // Xử lý dữ liệu (tự động sinh decision_number, ngày tháng, ...)
        $data = $this->processData($data);

        // Đảm bảo position_adjustments luôn tồn tại
        $data['position_adjustments'] = $data['position_adjustments'] ?? [
            'recipient_name_top' => '0',
            'program_top' => '0',
            'award_title_top' => '0',
            'award_title_english_top' => '0',
            'program_english_top' => '0',
            'vietnamese_date_top' => '0',
            'decision_number_top' => '0',
            'rector_name_top' => '0',
            'english_date_top' => '0',
        ];

        return view('certificate.pdf-template-pre', compact('data'));
    }

    /**
     * Check system status for debugging
     */
    public function checkSystemStatus()
    {
        $status = [
            'imagick_loaded' => extension_loaded('imagick'),
            'imagick_version' => extension_loaded('imagick') ? \Imagick::getVersion()['versionString'] : 'Not available',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'storage_writable' => is_writable(storage_path('app')),
            'temp_dir_exists' => is_dir(storage_path('app')),
        ];
        
        return response()->json($status);
    }

    /**
     * Generate JPG image from certificate template
     * New logic: Always create PDF first, then convert to JPG with high DPI
     */
    public function generateJpg(Request $request)
    {
        // Increase memory limit and execution time for image processing
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes
        
        try {
            // Validate input data
            $data = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'award_title' => 'required|string|max:255',
                'award_title_english' => 'required|string|max:500',
                'program' => 'required|string|max:255',
                'program_english' => 'nullable|string|max:255',
                'issue_day' => 'required|integer|min:1|max:31',
                'issue_month' => 'required|integer|min:1|max:12',
                'issue_year' => 'required|integer|min:2020|max:2030',
                'location' => 'required|string|max:100',
                'decision_prefix' => 'nullable|string|max:50',
                'decision_prefix_english' => 'nullable|string|max:50',
                'rector_name' => 'required|string|max:100',
                'position_adjustments' => 'nullable|array',
            ]);

            // Process data only once
            $processedData = $this->processData($data);

            // Generate a safe filename
            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $processedData['recipient_name']);
            $filename = 'Certificate_' . $cleanName . '_' . now()->format('Y-m-d_H-i-s') . '.jpg';

            // Step 1: Always create PDF first using the same logic as generatePdf
            $pdf = Pdf::view('certificate.pdf-template-pre', ['data' => $processedData])
                ->paperSize(223, 157) // 842x595px converted to mm
                ->margins(0, 0, 0, 0);

            // Save PDF temporarily
            $tempPdfPath = storage_path('app/temp_' . uniqid() . '.pdf');
            $pdf->save($tempPdfPath);

            // Step 2: Convert PDF to JPG with high DPI quality
            if (extension_loaded('imagick')) {
                try {
                    // Check if temp PDF file exists and is readable
                    if (!file_exists($tempPdfPath) || !is_readable($tempPdfPath)) {
                        throw new \Exception('Temp PDF file not found or not readable: ' . $tempPdfPath);
                    }
                    
                    $imagick = new \Imagick();
                    // Set resolution for good quality (300 DPI)
                    $imagick->setResolution(300, 300);
                    $imagick->readImage($tempPdfPath);
                    $imagick->setImageFormat('jpeg');
                    // Maximum quality compression
                    $imagick->setImageCompressionQuality(100);
                    // Additional quality optimizations
                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $imagick->setImageDepth(8);
                    $imagick = $imagick->flattenImages();

                    // Resize image to width = 1192px, height will be calculated proportionally
                    $currentWidth = $imagick->getImageWidth();
                    $currentHeight = $imagick->getImageHeight();
                    $targetWidth = 1192;
                    
                    if ($currentWidth > $targetWidth) {
                        $targetHeight = intval(($currentHeight * $targetWidth) / $currentWidth);
                        $imagick->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_LANCZOS, 1);
                    }

                    // Clean up temp PDF - COMMENTED OUT TO KEEP TMP FILES
                    // unlink($tempPdfPath);

                    // Return JPG as response
                    return response($imagick->getImageBlob(), 200)
                        ->header('Content-Type', 'image/jpeg')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');

                } catch (\Exception $e) {
                // Clean up temp PDF on error - COMMENTED OUT TO KEEP TMP FILES
                // if (file_exists($tempPdfPath)) {
                //     unlink($tempPdfPath);
                // }
                    \Log::error('Error converting PDF to JPG with Imagick', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return back()->withErrors(['error' => 'Lỗi chuyển đổi PDF sang JPG: ' . $e->getMessage()]);
                }
            } else {
                // If no Imagick, return PDF instead - COMMENTED OUT deleteFileAfterSend TO KEEP TMP FILES
                return response()->download($tempPdfPath, str_replace('.jpg', '.pdf', $filename));
            }

        } catch (\Exception $e) {
            \Log::error('JPG generation failed at validation or pre-process', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Lỗi tạo JPG: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate PDF with viewport exactly matching the certificate frame,
     * no extra white space, using custom page size (1813x1300px at 96dpi).
     */
    /**
     * Generate PDF, dùng để loại bỏ trang thứ 2 trở lên trong file PDF.
     * Chỉ giữ lại trang đầu tiên của file PDF xuất ra.
     */
    public function generatePdf(Request $request)
    {
        try {
            // Validate input data
            $data = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'award_title' => 'required|string|max:255',
                'award_title_english' => 'required|string|max:500',
                'program' => 'required|string|max:255',
                'program_english' => 'nullable|string|max:255',
                'issue_day' => 'required|integer|min:1|max:31',
                'issue_month' => 'required|integer|min:1|max:12',
                'issue_year' => 'required|integer|min:2020|max:2030',
                'location' => 'required|string|max:100',
                'decision_prefix' => 'nullable|string|max:50',
                'decision_prefix_english' => 'nullable|string|max:50',
                'rector_name' => 'required|string|max:100',
                'position_adjustments' => 'nullable|array',
            ]);

            // Process data only once
            $processedData = $this->processData($data);

            // Generate a safe filename
            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $processedData['recipient_name']);
            $filename = 'Certificate_' . $cleanName . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            // Generate PDF and return as response
            try {
                $pdf = Pdf::view('certificate.pdf-template-pre', ['data' => $processedData])
                    ->paperSize(223, 157) // 842x595px converted to mm
                    ->margins(0, 0, 0, 0);

                return $pdf->save($filename);
            } catch (\Exception $e) {
                \Log::error('Unexpected error during PDF generation', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return back()->withErrors(['error' => 'Lỗi tạo PDF: ' . $e->getMessage()]);
            }
        } catch (\Exception $e) {
            \Log::error('PDF generation failed at validation or pre-process', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Lỗi tạo PDF: ' . $e->getMessage()]);
        }
    }

    public function create()
    {
        return view('certificate.create-unified');
    }
    public function bulk()
    {
        return view('certificate.create-unified');
    }

    /**
     * Unified method to handle both single and bulk certificate generation
     */
    public function generateUnified(Request $request)
    {
        // Check if Excel file is uploaded (bulk mode)
        if ($request->hasFile('excel_file')) {
            return $this->generateBulkPdf($request);
        } else {
            // Single mode - validate and generate single PDF
            return $this->generatePdf($request);
        }
    }

    /**
     * Unified method to handle both single and bulk JPG generation
     */
    public function generateUnifiedJpg(Request $request)
    {
        // Check if Excel file is uploaded (bulk mode)
        if ($request->hasFile('excel_file')) {
            return $this->generateBulkJpg($request);
        } else {
            // Single mode - validate and generate single JPG
            return $this->generateJpg($request);
        }
    }
    
    /**
     * Generate bulk JPGs from an Excel file and return as a ZIP.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateBulkJpg(Request $request)
    {
        // Increase memory limit and execution time for bulk processing
        ini_set('memory_limit', '1024M');
        set_time_limit(600); // 10 minutes
        
        // Validate the uploaded Excel file
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        $filePath = $request->file('excel_file')->getRealPath();
        $tempDir = $this->createTempDirectory();

        try {
            // Load and parse Excel data
            $data = $this->loadExcelData($filePath);
            $header = $data['header'];
            $rows = $data['rows'];
            $mssvCol = $this->findMssvColumn($header);

            // Generate JPGs
            $jpgFiles = $this->generateJpgs($rows, $header, $mssvCol, $tempDir);

            // Create ZIP file
            $zipFilePath = $this->createZipFile($jpgFiles);

            // Clean up temporary files
            $this->cleanupTempFiles($jpgFiles, $tempDir);

            // Return ZIP file for download - COMMENTED OUT deleteFileAfterSend TO KEEP TMP FILES
            return response()->download($zipFilePath);
        } catch (\Exception $e) {
            $this->cleanupTempFiles([], $tempDir);
            Log::error('Error in generateBulkJpg', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Lỗi xử lý: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate bulk PDFs from an Excel file and return as a ZIP.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateBulkPdf(Request $request)
    {
        // Increase memory limit and execution time for bulk processing
        ini_set('memory_limit', '1024M');
        set_time_limit(600); // 10 minutes
        
        // Validate the uploaded Excel file
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        $filePath = $request->file('excel_file')->getRealPath();
        $tempDir = $this->createTempDirectory();

        try {
            // Load and parse Excel data
            $data = $this->loadExcelData($filePath);
            $header = $data['header'];
            $rows = $data['rows'];
            $mssvCol = $this->findMssvColumn($header);

            // Generate PDFs
            $pdfFiles = $this->generatePdfs($rows, $header, $mssvCol, $tempDir);

            // Create ZIP file
            $zipFilePath = $this->createZipFile($pdfFiles);

            // Clean up temporary files
            $this->cleanupTempFiles($pdfFiles, $tempDir);

            // Return ZIP file for download - COMMENTED OUT deleteFileAfterSend TO KEEP TMP FILES
            return response()->download($zipFilePath);
        } catch (\Exception $e) {
            $this->cleanupTempFiles([], $tempDir);
            Log::error('Error in generateBulkPdf', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Lỗi xử lý: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a temporary directory for storing PDFs.
     *
     * @return string
     */
    private function createTempDirectory(): string
    {
        $tempDir = storage_path('app/tmp_bulk_pdfs_' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        return $tempDir;
    }

    /**
     * Load and parse Excel file data.
     *
     * @param string $filePath
     * @return array
     * @throws \Exception
     */
    private function loadExcelData(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            $header = array_shift($rows);
            return ['header' => $header, 'rows' => $rows];
        } catch (\Exception $e) {
            Log::error('Error loading Excel file', ['error' => $e->getMessage()]);
            throw new \Exception('Lỗi xử lý file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Find the column index for 'mssv' (case-insensitive).
     *
     * @param array $header
     * @return string|null
     */
    private function findMssvColumn(array $header): ?string
    {
        foreach ($header as $col => $field) {
            if (strtolower(trim($field)) === 'mssv') {
                return $col;
            }
        }
        return null;
    }

    /**
     * Generate JPGs for each row.
     *
     * @param array $rows
     * @param array $header
     * @param string|null $mssvCol
     * @param string $tempDir
     * @return array
     * @throws \Exception
     */
    private function generateJpgs(array $rows, array $header, ?string $mssvCol, string $tempDir): array
    {
        $jpgFiles = [];

        foreach ($rows as $index => $row) {
            // Map row data to fields
            $data = $this->mapRowData($row, $header);
            $processedData = $this->processData($data);

            // Generate filename
            $filename = $this->generateFilename($row, $mssvCol, $processedData['recipient_name'] ?? 'unknown', $index, 'jpg');
            $filePathJpg = $tempDir . DIRECTORY_SEPARATOR . $filename;

            try {
                // Generate JPG
                $this->generateSingleJpg($processedData, $filePathJpg);
                $jpgFiles[] = $filePathJpg;
            } catch (\Exception $e) {
                Log::error('Error generating JPG', [
                    'filename' => $filename,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->cleanupTempFiles($jpgFiles, $tempDir);
                throw new \Exception('Lỗi tạo JPG: ' . $e->getMessage());
            }
        }

        return $jpgFiles;
    }

    /**
     * Generate PDFs for each row and ensure single-page output.
     *
     * @param array $rows
     * @param array $header
     * @param string|null $mssvCol
     * @param string $tempDir
     * @return array
     * @throws \Exception
     */
    private function generatePdfs(array $rows, array $header, ?string $mssvCol, string $tempDir): array
    {
        $pdfFiles = [];

        foreach ($rows as $index => $row) {
            // Map row data to fields
            $data = $this->mapRowData($row, $header);
            $processedData = $this->processData($data);

            // Generate filename
            $filename = $this->generateFilename($row, $mssvCol, $processedData['recipient_name'] ?? 'unknown', $index);
            $filePathPdf = $tempDir . DIRECTORY_SEPARATOR . $filename;

            try {
                // Generate PDF and enforce single page
                $this->generateSinglePagePdf($processedData, $filePathPdf);
                $pdfFiles[] = $filePathPdf;
            } catch (\Exception $e) {
                Log::error('Error generating PDF', [
                    'filename' => $filename,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->cleanupTempFiles($pdfFiles, $tempDir);
                throw new \Exception('Lỗi tạo PDF: ' . $e->getMessage());
            }
        }

        return $pdfFiles;
    }

    /**
     * Map row data to field names.
     *
     * @param array $row
     * @param array $header
     * @return array
     */
    private function mapRowData(array $row, array $header): array
    {
        $data = [];
        foreach ($header as $col => $field) {
            $data[$field] = $row[$col] ?? null;
        }
        return array_merge([
            'recipient_name' => null,
            'award_title' => null,
            'academic_year' => null,
            'award_title_english' => null,
            'program' => null,
            'program_english' => null,
            'issue_day' => null,
            'issue_month' => null,
            'issue_year' => null,
            'location' => null,
            'decision_prefix' => null,
            'decision_prefix_english' => null,            
            'rector_name' => null,
            'position_adjustments' => null,
        ], $data);
    }

    /**
     * Generate a safe filename for the PDF or JPG.
     *
     * @param array $row
     * @param string|null $mssvCol
     * @param string $recipientName
     * @param int $index
     * @param string $extension
     * @return string
     */
    private function generateFilename(array $row, ?string $mssvCol, string $recipientName, int $index, string $extension = 'pdf'): string
    {
        if ($mssvCol !== null && !empty($row[$mssvCol])) {
            $mssvValue = preg_replace('/[^A-Za-z0-9_\-]/', '_', $row[$mssvCol]);
            return $mssvValue . '.' . $extension;
        }
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $recipientName);
        return 'Certificate_' . $cleanName . '_' . ($index + 1) . '.' . $extension;
    }

    /**
     * Generate a single JPG using the new logic: Always create PDF first, then convert to JPG with high DPI.
     *
     * @param array $data
     * @param string $filePathJpg
     * @return void
     * @throws \Exception
     */
    private function generateSingleJpg(array $data, string $filePathJpg): void
    {
        // Step 1: Always create PDF first using the same logic as generatePdf
        $pdf = Pdf::view('certificate.pdf-template-pre', ['data' => $data])
            ->paperSize(223, 157) // 842x595px converted to mm
            ->margins(0, 0, 0, 0);

        // Save PDF temporarily
        $tempPdfPath = storage_path('app/temp_' . uniqid() . '.pdf');
        $pdf->save($tempPdfPath);

        // Step 2: Convert PDF to JPG with high DPI quality using helper method
        $this->convertPdfToJpg($tempPdfPath, $filePathJpg);
    }

    /**
     * Convert PDF to JPG with optimized high-quality settings
     *
     * @param string $pdfPath
     * @param string $jpgPath
     * @return void
     * @throws \Exception
     */
    private function convertPdfToJpg(string $pdfPath, string $jpgPath): void
    {
        if (extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick();
                // Set resolution for good quality (300 DPI)
                $imagick->setResolution(300, 300);
                $imagick->readImage($pdfPath);
                $imagick->setImageFormat('jpeg');
                // Maximum quality compression
                $imagick->setImageCompressionQuality(100);
                // Additional quality optimizations
                $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $imagick->setImageDepth(8);
                // Flatten layers to ensure single image output
                $imagick = $imagick->flattenImages();

                // Resize image to width = 1920px, height will be calculated proportionally
                $currentWidth = $imagick->getImageWidth();
                $currentHeight = $imagick->getImageHeight();
                $targetWidth = 1192;
                
                if ($currentWidth > $targetWidth) {
                    $targetHeight = intval(($currentHeight * $targetWidth) / $currentWidth);
                    $imagick->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_LANCZOS, 1);
                }

                // Save JPG file
                $imagick->writeImage($jpgPath);

                // Clean up temp PDF - COMMENTED OUT TO KEEP TMP FILES
                // unlink($pdfPath);
                
            } catch (\Exception $e) {
                // Clean up temp PDF on error - COMMENTED OUT TO KEEP TMP FILES
                // if (file_exists($pdfPath)) {
                //     unlink($pdfPath);
                // }
                throw new \Exception('Lỗi chuyển đổi PDF sang JPG: ' . $e->getMessage());
            }
        } else {
            // If no Imagick, copy PDF file with JPG extension (fallback) - COMMENTED OUT TO KEEP TMP FILES
            copy($pdfPath, $jpgPath);
            // unlink($pdfPath);
        }
    }

    /**
     * Generate a single-page PDF using Spatie and enforce single page with FPDI.
     *
     * @param array $data
     * @param string $filePathPdf
     * @return void
     * @throws \Exception
     */
    private function generateSinglePagePdf(array $data, string $filePathPdf): void
    {
        // Generate initial PDF with custom Chromium path
        Pdf::view('certificate.pdf-template-pre', ['data' => $data])
            ->paperSize(223, 157) // 842x595px converted to mm
            ->margins(0, 0, 0, 0)
            ->save($filePathPdf);

        // Enforce single page using FPDI
        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($filePathPdf);

            if ($pageCount > 1) {
                // Create a new PDF with only the first page
                $pdf->AddPage();
                $templateId = $pdf->importPage(1);
                $pdf->useTemplate($templateId);
                $pdf->Output($filePathPdf, 'F');
            }
        } catch (\Exception $e) {
            // If FPDI fails, keep the original PDF
            \Log::warning('FPDI single page enforcement failed, keeping original PDF', [
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create a ZIP file from generated PDFs.
     *
     * @param array $pdfFiles
     * @return string
     * @throws \Exception
     */
    private function createZipFile(array $pdfFiles): string
    {
        $zipFileName = 'certificates_' . now()->format('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === true) {
            foreach ($pdfFiles as $pdfFile) {
                $localName = basename($pdfFile);
                $zip->addFile($pdfFile, $localName);
            }
            $zip->close();
        } else {
            throw new \Exception('Không thể tạo file ZIP');
        }

        return $zipFilePath;
    }

    /**
     * Clean up temporary files and directory.
     *
     * @param array $pdfFiles
     * @param string $tempDir
     * @return void
     */
    private function cleanupTempFiles(array $pdfFiles, string $tempDir): void
    {
        // COMMENTED OUT TO KEEP TMP FILES FOR MANUAL DELETION
        // foreach ($pdfFiles as $pdfFile) {
        //     if (file_exists($pdfFile)) {
        //         unlink($pdfFile);
        //     }
        // }
        // if (file_exists($tempDir)) {
        //     rmdir($tempDir);
        // }
    }

    /**
     * Generate multiple certificates from Excel data and return as a ZIP file.
     */
    //  public function generateBulkPdf(Request $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls'
    //     ]);

    //     $file = $request->file('excel_file');
    //     $filePath = $file->getRealPath();

    //     try {
    //         // Load the spreadsheet
    //         $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
    //         $sheet = $spreadsheet->getActiveSheet();
    //         $rows = $sheet->toArray(null, true, true, true);

    //         // Get header and rows
    //         $header = array_shift($rows);

    //         // Map header columns to field names
    //         $fieldMap = $header;
    //         $pdfFiles = [];

    //         // Xác định cột MSSV (tìm key có giá trị là 'mssv' không phân biệt hoa thường)
    //         $mssvCol = null;
    //         foreach ($fieldMap as $col => $field) {
    //             if (strtolower(trim($field)) === 'mssv') {
    //                 $mssvCol = $col;
    //                 break;
    //             }
    //         }

    //         // Tạo thư mục tạm để lưu các file PDF
    //         $tempDir = storage_path('app/tmp_bulk_pdfs_' . uniqid());
    //         if (!file_exists($tempDir)) {
    //             mkdir($tempDir, 0777, true);
    //         }

    //         foreach ($rows as $index => $row) {
    //             // Build data array for generatePdf
    //             $data = [];
    //             foreach ($fieldMap as $col => $field) {
    //                 $data[$field] = isset($row[$col]) ? $row[$col] : null;
    //             }

    //             // Fill missing fields with null if not present
    //             $data = array_merge([
    //                 'recipient_name' => null,
    //                 'award_title' => null,
    //                 'academic_year' => null,
    //                 'award_title_english' => null,
    //                 'program' => null,
    //                 'program_english' => null,
    //                 'issue_day' => null,
    //                 'issue_month' => null,
    //                 'issue_year' => null,
    //                 'location' => null,
    //                 'decision_prefix' => null,
    //                 'rector_name' => null,
    //                 'position_adjustments' => null,
    //             ], $data);

    //             // Process data as in generatePdf
    //             $processedData = $this->processData($data);

    //             // Generate a safe filename using MSSV if available, otherwise fallback to recipient_name
    //             $mssvValue = null;
    //             if ($mssvCol !== null && isset($row[$mssvCol]) && !empty($row[$mssvCol])) {
    //                 $mssvValue = preg_replace('/[^A-Za-z0-9_\-]/', '_', $row[$mssvCol]);
    //             }
    //             if (!$mssvValue) {
    //                 $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $processedData['recipient_name'] ?? 'unknown');
    //                 $filename = 'Certificate_' . $cleanName . '_' . ($index + 1) . '.pdf';
    //             } else {
    //                 $filename = 'Certificate_' . $mssvValue . '.pdf';
    //             }
    //             $filePathPdf = $tempDir . DIRECTORY_SEPARATOR . $filename;

    //             try {
    //                 // Sử dụng Spatie\LaravelPdf\Facades\Pdf để tạo file PDF và lưu vào thư mục tạm
    //                 Pdf::view('certificate.pdf-template-pre', ['data' => $processedData])
    //                     ->paperSize(177.7, 126.0)
    //                     ->margins(0, 0, 0, 0)
    //                     ->save($filePathPdf);

    //                 $pdfFiles[] = $filePathPdf;
    //             } catch (\Exception $e) {
    //                 \Log::error('Unexpected error during PDF generation', [
    //                     'message' => $e->getMessage(),
    //                     'trace' => $e->getTraceAsString(),
    //                 ]);
    //                 // Xóa thư mục tạm nếu có lỗi
    //                 foreach ($pdfFiles as $pdfFile) {
    //                     if (file_exists($pdfFile)) unlink($pdfFile);
    //                 }
    //                 if (file_exists($tempDir)) rmdir($tempDir);
    //                 return back()->withErrors(['error' => 'Lỗi tạo PDF: ' . $e->getMessage()]);
    //             }
    //         }

    //         // Tạo file ZIP từ các file PDF
    //         $zipFileName = 'certificates_' . now()->format('Ymd_His') . '.zip';
    //         $zipFilePath = storage_path('app/' . $zipFileName);

    //         $zip = new \ZipArchive();
    //         if ($zip->open($zipFilePath, \ZipArchive::CREATE) === TRUE) {
    //             foreach ($pdfFiles as $pdfFile) {
    //                 $localName = basename($pdfFile);
    //                 $zip->addFile($pdfFile, $localName);
    //             }
    //             $zip->close();
    //         } else {
    //             // Xóa file tạm nếu có lỗi
    //             foreach ($pdfFiles as $pdfFile) {
    //                 if (file_exists($pdfFile)) unlink($pdfFile);
    //             }
    //             if (file_exists($tempDir)) rmdir($tempDir);
    //             return back()->withErrors(['error' => 'Không thể tạo file ZIP']);
    //         }

    //         // Xóa các file PDF tạm
    //         foreach ($pdfFiles as $pdfFile) {
    //             if (file_exists($pdfFile)) unlink($pdfFile);
    //         }
    //         if (file_exists($tempDir)) rmdir($tempDir);

    //         // Trả file ZIP về cho người dùng
    //         return response()->download($zipFilePath)->deleteFileAfterSend(true);

    //     } catch (\Exception $e) {
    //         \Log::error('Error processing Excel file', ['error' => $e->getMessage()]);
    //         return back()->withErrors(['error' => 'Lỗi xử lý file Excel: ' . $e->getMessage()]);
    //     }
    // }
    public function preview(Request $request)
    {
        $data = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'award_title' => 'required|string|max:255',
            'academic_year' => 'required|string|max:50',
            'award_title_english' => 'required|string|max:500',
            'program' => 'required|string|max:255',
            'program_english' => 'nullable|string|max:255',
            'issue_day' => 'required|integer|min:1|max:31',
            'issue_month' => 'required|integer|min:1|max:12',
            'issue_year' => 'required|integer|min:2020|max:2030',
            'location' => 'required|string|max:100',
            'decision_prefix' => 'nullable|string|max:50',
            'decision_prefix_english' => 'nullable|string|max:50',
            'rector_name' => 'required|string|max:100',
        ]);

        $processedData = $this->processData($data);

        return view('certificate.preview', [
            'data' => $processedData
        ]);
    }

    /**
     * Process and format form data
     */
    private function processData($data)
    {
        $data['recipient_name'] = strtoupper(trim($data['recipient_name'] ?? ''));
        $data['program'] = strtoupper(trim($data['program'] ?? ''));

        $issueDate = Carbon::createFromDate(
            $data['issue_year'],
            $data['issue_month'],
            $data['issue_day']
        );

        $data['decision_number'] = trim($data['decision_prefix'] ?? '');
        $data['decision_prefix_english'] = trim($data['decision_prefix_english'] ?? '');

        $data['vietnamese_date'] = $data['location'] . ', ngày ' . $data['issue_day']
            . ' tháng ' . $data['issue_month']
            . ' năm ' . $data['issue_year'];

        // Map all 63 Vietnamese provinces/cities to English for the English date
        $locationMap = [
            'An Giang' => 'An Giang',
            'Bà Rịa - Vũng Tàu' => 'Ba Ria - Vung Tau',
            'Bắc Giang' => 'Bac Giang',
            'Bắc Kạn' => 'Bac Kan',
            'Bạc Liêu' => 'Bac Lieu',
            'Bắc Ninh' => 'Bac Ninh',
            'Bến Tre' => 'Ben Tre',
            'Bình Định' => 'Binh Dinh',
            'Bình Dương' => 'Binh Duong',
            'Bình Phước' => 'Binh Phuoc',
            'Bình Thuận' => 'Binh Thuan',
            'Cà Mau' => 'Ca Mau',
            'Cần Thơ' => 'Can Tho',
            'Cao Bằng' => 'Cao Bang',
            'Đà Nẵng' => 'Da Nang',
            'Đắk Lắk' => 'Dak Lak',
            'Đắk Nông' => 'Dak Nong',
            'Điện Biên' => 'Dien Bien',
            'Đồng Nai' => 'Dong Nai',
            'Đồng Tháp' => 'Dong Thap',
            'Gia Lai' => 'Gia Lai',
            'Hà Giang' => 'Ha Giang',
            'Hà Nam' => 'Ha Nam',
            'Hà Nội' => 'Ha Noi',
            'Hà Tĩnh' => 'Ha Tinh',
            'Hải Dương' => 'Hai Duong',
            'Hải Phòng' => 'Hai Phong',
            'Hậu Giang' => 'Hau Giang',
            'Hòa Bình' => 'Hoa Binh',
            'Hưng Yên' => 'Hung Yen',
            'Khánh Hòa' => 'Khanh Hoa',
            'Kiên Giang' => 'Kien Giang',
            'Kon Tum' => 'Kon Tum',
            'Lai Châu' => 'Lai Chau',
            'Lâm Đồng' => 'Lam Dong',
            'Lạng Sơn' => 'Lang Son',
            'Lào Cai' => 'Lao Cai',
            'Long An' => 'Long An',
            'Nam Định' => 'Nam Dinh',
            'Nghệ An' => 'Nghe An',
            'Ninh Bình' => 'Ninh Binh',
            'Ninh Thuận' => 'Ninh Thuan',
            'Phú Thọ' => 'Phu Tho',
            'Phú Yên' => 'Phu Yen',
            'Quảng Bình' => 'Quang Binh',
            'Quảng Nam' => 'Quang Nam',
            'Quảng Ngãi' => 'Quang Ngai',
            'Quảng Ninh' => 'Quang Ninh',
            'Quảng Trị' => 'Quang Tri',
            'Sóc Trăng' => 'Soc Trang',
            'Sơn La' => 'Son La',
            'Tây Ninh' => 'Tay Ninh',
            'Thái Bình' => 'Thai Binh',
            'Thái Nguyên' => 'Thai Nguyen',
            'Thanh Hóa' => 'Thanh Hoa',
            'Thừa Thiên Huế' => 'Thua Thien Hue',
            'Tiền Giang' => 'Tien Giang',
            'TP Hồ Chí Minh' => 'Ho Chi Minh City',
            'Trà Vinh' => 'Tra Vinh',
            'Tuyên Quang' => 'Tuyen Quang',
            'Vĩnh Long' => 'Vinh Long',
            'Vĩnh Phúc' => 'Vinh Phuc',
            'Yên Bái' => 'Yen Bai',
        ];
        $englishLocation = $data['location'];
        if (isset($locationMap[$englishLocation])) {
            $englishLocation = $locationMap[$englishLocation];
        } else {
            // Remove Vietnamese accents for unmapped locations
            $englishLocation = iconv('UTF-8', 'ASCII//TRANSLIT', $englishLocation);
        }
        $data['english_date'] = '(' . $englishLocation . ', ' . $issueDate->format('F j, Y') . ')';

        $data['rector_name'] = trim($data['rector_name'] ?? '');

        if (!empty($data['program_english'] ?? '')) {
            $data['program_english'] = trim($data['program_english']);
        }
        return $data;
    }

    /**
     * AJAX endpoint for real-time preview updates
     */
    public function previewAjax(Request $request)
    {
        try {
            $data = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'award_title' => 'required|string|max:255',
                'award_title_english' => 'required|string|max:500',
                'program' => 'required|string|max:255',
                'program_english' => 'nullable|string|max:255',
                'issue_day' => 'required|integer|min:1|max:31',
                'issue_month' => 'required|integer|min:1|max:12',
                'issue_year' => 'required|integer|min:2020|max:2030',
                'location' => 'required|string|max:100',
                'decision_prefix' => 'nullable|string|max:50',
                'decision_prefix_english' => 'nullable|string|max:50',
                'rector_name' => 'required|string|max:100',
            ]);

            $processedData = $this->processData($data);

            return response()->json([
                'success' => true,
                'data' => $processedData,
                'html' => view('certificate.preview-content', [
                    'data' => $processedData
                ])->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \Illuminate\Validation\ValidationException
                    ? $e->errors()
                    : ['general' => [$e->getMessage()]]
            ], 422);
        }
    }

    // ==================== NEW DOCTOR CERTIFICATE METHODS ====================

    /**
     * Show new doctor certificate creation form
     */
    public function newStudentCreate()
    {
        // Lấy danh sách ảnh nền từ thư mục newDoctorTemplate
        $backgroundImages = [];
        $templatePath = public_path('assets/newDoctorTemplate');
        
        if (is_dir($templatePath)) {
            $files = scandir($templatePath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'jpg' || pathinfo($file, PATHINFO_EXTENSION) === 'png') {
                    $backgroundImages[] = [
                        'filename' => $file,
                        'display_name' => pathinfo($file, PATHINFO_FILENAME),
                        'path' => 'assets/newDoctorTemplate/' . $file
                    ];
                }
            }
        }
        
        return view('certificate.new-student-create', compact('backgroundImages'));
    }

    /**
     * Preview new doctor certificate
     */
    public function newStudentPreviewPdf(Request $request)
    {
        $data = $request->validate([
            'student_name' => 'required|string|max:255',
            'background_image' => 'required|string',
        ]);

        // Process data
        $processedData = [
            'student_name' => strtoupper(trim($data['student_name'])),
            'background_image' => $data['background_image']
        ];

        // Check if background image file exists
        $backgroundPath = public_path('assets/newDoctorTemplate/' . $processedData['background_image']);
        if (!file_exists($backgroundPath)) {
            \Log::error('Background image not found for preview', ['path' => $backgroundPath]);
            return back()->withErrors(['error' => 'Ảnh nền không tồn tại: ' . $processedData['background_image']]);
        }

        return view('certificate.new-student-template', compact('processedData'));
    }

    /**
     * Generate new doctor PDF
     */
    public function newStudentGeneratePdf(Request $request)
    {
        try {
            $data = $request->validate([
                'student_name' => 'required|string|max:255',
                'background_image' => 'required|string',
            ]);

            // Process data
            $processedData = [
                'student_name' => strtoupper(trim($data['student_name'])),
                'background_image' => $data['background_image']
            ];

            // Check if background image file exists
            $backgroundPath = public_path('assets/newDoctorTemplate/' . $processedData['background_image']);
            if (!file_exists($backgroundPath)) {
                \Log::error('Background image not found', ['path' => $backgroundPath]);
                return back()->withErrors(['error' => 'Ảnh nền không tồn tại: ' . $processedData['background_image']]);
            }

            // Generate a safe filename
            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $processedData['student_name']);
            $filename = 'NewStudent_' . $cleanName . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            // Generate PDF
            $pdf = Pdf::view('certificate.new-student-template', ['processedData' => $processedData])
                ->paperSize(315, 392) // 1192x1482px converted to mm (1192/3.78, 1482/3.78)
                ->margins(0, 0, 0, 0);

            return $pdf->save($filename);
        } catch (\Exception $e) {
            \Log::error('New Student PDF generation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Lỗi tạo PDF: ' . $e->getMessage()]);
        }
    }

    /**
     * Unified method to handle both single and bulk New Student generation
     */
    public function newStudentGenerateUnified(Request $request)
    {
        // Check if Excel file is uploaded (bulk mode)
        if ($request->hasFile('excel_file')) {
            return $this->newStudentGenerateBulkJpg($request);
        } else {
            // Single mode - validate and generate single JPG
            return $this->newStudentGenerateJpg($request);
        }
    }

    /**
     * Generate new doctor JPG
     */
    public function newStudentGenerateJpg(Request $request)
    {
        // Increase memory limit and execution time for image processing
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes
        
        try {
            $data = $request->validate([
                'student_name' => 'required|string|max:255',
                'background_image' => 'required|string',
            ]);

            // Process data
            $processedData = [
                'student_name' => strtoupper(trim($data['student_name'])),
                'background_image' => $data['background_image']
            ];

            // Check if background image file exists
            $backgroundPath = public_path('assets/newDoctorTemplate/' . $processedData['background_image']);
            if (!file_exists($backgroundPath)) {
                \Log::error('Background image not found', ['path' => $backgroundPath]);
                return back()->withErrors(['error' => 'Ảnh nền không tồn tại: ' . $processedData['background_image']]);
            }

            // Generate a safe filename
            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $processedData['student_name']);
            $filename = 'NewStudent_' . $cleanName . '_' . now()->format('Y-m-d_H-i-s') . '.jpg';

            // Step 1: Create PDF first
            $pdf = Pdf::view('certificate.new-student-template', ['processedData' => $processedData])
                ->paperSize(315, 392) // 1192x1482px converted to mm (1192/3.78, 1482/3.78)
                ->margins(0, 0, 0, 0);

            // Save PDF temporarily
            $tempPdfPath = storage_path('app/temp_' . uniqid() . '.pdf');
            $pdf->save($tempPdfPath);

            // Step 2: Convert PDF to JPG with high DPI quality
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new \Imagick();
                    $imagick->setResolution(300, 300);
                    $imagick->readImage($tempPdfPath);
                    $imagick->setImageFormat('jpeg');
                    $imagick->setImageCompressionQuality(100);
                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $imagick->setImageDepth(8);
                    $imagick = $imagick->flattenImages();

                    // Resize image to width = 1192px
                    $currentWidth = $imagick->getImageWidth();
                    $currentHeight = $imagick->getImageHeight();
                    $targetWidth = 1192;
                    
                    if ($currentWidth > $targetWidth) {
                        $targetHeight = intval(($currentHeight * $targetWidth) / $currentWidth);
                        $imagick->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_LANCZOS, 1);
                    }

                    // Clean up temp PDF - COMMENTED OUT TO KEEP TMP FILES
                    // unlink($tempPdfPath);

                    // Return JPG as response
                    return response($imagick->getImageBlob(), 200)
                        ->header('Content-Type', 'image/jpeg')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');

                } catch (\Exception $e) {
                // Clean up temp PDF on error - COMMENTED OUT TO KEEP TMP FILES
                // if (file_exists($tempPdfPath)) {
                //     unlink($tempPdfPath);
                // }
                    \Log::error('Error converting PDF to JPG with Imagick', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return back()->withErrors(['error' => 'Lỗi chuyển đổi PDF sang JPG: ' . $e->getMessage()]);
                }
            } else {
                // If no Imagick, return PDF instead - COMMENTED OUT deleteFileAfterSend TO KEEP TMP FILES
                return response()->download($tempPdfPath, str_replace('.jpg', '.pdf', $filename));
            }

        } catch (\Exception $e) {
            \Log::error('New Student JPG generation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Lỗi tạo JPG: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate bulk New Student JPGs from Excel file
     */
    public function newStudentGenerateBulkJpg(Request $request)
    {
        // Increase memory limit and execution time for bulk processing
        ini_set('memory_limit', '1024M');
        set_time_limit(600); // 10 minutes
        
        // Validate the uploaded Excel file
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        $filePath = $request->file('excel_file')->getRealPath();
        $tempDir = $this->createTempDirectory();

        try {
            // Load and parse Excel data
            $data = $this->loadExcelData($filePath);
            $header = $data['header'];
            $rows = $data['rows'];

            // Validate required columns
            $requiredColumns = ['MSSV', 'DANH HIỆU', 'HỌ VÀ TÊN'];
            $columnMapping = $this->mapNewStudentColumns($header, $requiredColumns);

            // Generate JPGs
            $jpgFiles = $this->generateNewStudentJpgs($rows, $columnMapping, $tempDir);

            // Create ZIP file
            $zipFilePath = $this->createZipFile($jpgFiles);

            // Clean up temporary files
            $this->cleanupTempFiles($jpgFiles, $tempDir);

            // Return ZIP file for download - COMMENTED OUT deleteFileAfterSend TO KEEP TMP FILES
            return response()->download($zipFilePath);
        } catch (\Exception $e) {
            $this->cleanupTempFiles([], $tempDir);
            Log::error('Error in newStudentGenerateBulkJpg', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Lỗi xử lý: ' . $e->getMessage()]);
        }
    }

    /**
     * Map Excel columns to required fields for New Student
     */
    private function mapNewStudentColumns(array $header, array $requiredColumns): array
    {
        $mapping = [];
        $missingColumns = [];

        foreach ($requiredColumns as $required) {
            $found = false;
            foreach ($header as $col => $field) {
                if (strtolower(trim($field)) === strtolower(trim($required))) {
                    $mapping[$required] = $col;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingColumns[] = $required;
            }
        }

        if (!empty($missingColumns)) {
            throw new \Exception('Thiếu các cột bắt buộc: ' . implode(', ', $missingColumns));
        }

        return $mapping;
    }

    /**
     * Generate New Student JPGs for each row
     */
    private function generateNewStudentJpgs(array $rows, array $columnMapping, string $tempDir): array
    {
        $jpgFiles = [];
        $titleMapping = [
            'TÂN BÁC SĨ' => 'TÂN BÁC SĨ.jpg',
            'TÂN DƯỢC SĨ' => 'TÂN DƯỢC SĨ.jpg',
            'TÂN CỬ NHÂN' => 'TÂN CỬ NHÂN.jpg'
        ];

        foreach ($rows as $index => $row) {
            try {
                // Extract data from row
                $mssv = trim($row[$columnMapping['MSSV']] ?? '');
                $danhHieu = trim($row[$columnMapping['DANH HIỆU']] ?? '');
                $hoTen = trim($row[$columnMapping['HỌ VÀ TÊN']] ?? '');

                // Validate required data
                if (empty($mssv) || empty($danhHieu) || empty($hoTen)) {
                    Log::warning('Skipping row with missing data', [
                        'row' => $index + 1,
                        'mssv' => $mssv,
                        'danh_hieu' => $danhHieu,
                        'ho_ten' => $hoTen
                    ]);
                    continue;
                }

                // Map danh hieu to background image
                if (!isset($titleMapping[$danhHieu])) {
                    Log::warning('Unknown danh hieu', [
                        'row' => $index + 1,
                        'danh_hieu' => $danhHieu
                    ]);
                    continue;
                }

                $backgroundImage = $titleMapping[$danhHieu];

                // Check if background image exists
                $backgroundPath = public_path('assets/newDoctorTemplate/' . $backgroundImage);
                if (!file_exists($backgroundPath)) {
                    Log::error('Background image not found', [
                        'row' => $index + 1,
                        'background' => $backgroundImage,
                        'path' => $backgroundPath
                    ]);
                    continue;
                }

                // Prepare data for template
                $processedData = [
                    'student_name' => strtoupper($hoTen),
                    'background_image' => $backgroundImage
                ];

                // Generate filename using MSSV
                $cleanMssv = preg_replace('/[^A-Za-z0-9_\-]/', '_', $mssv);
                $filename = $cleanMssv . '.jpg';
                $filePathJpg = $tempDir . DIRECTORY_SEPARATOR . $filename;

                // Generate JPG
                $this->generateSingleNewStudentJpg($processedData, $filePathJpg);
                $jpgFiles[] = $filePathJpg;

                Log::info('Generated New Student JPG', [
                    'mssv' => $mssv,
                    'filename' => $filename,
                    'danh_hieu' => $danhHieu
                ]);

            } catch (\Exception $e) {
                Log::error('Error generating New Student JPG', [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continue with next row instead of stopping
                continue;
            }
        }

        return $jpgFiles;
    }

    /**
     * Generate a single New Student JPG
     */
    private function generateSingleNewStudentJpg(array $data, string $filePathJpg): void
    {
        // Step 1: Create PDF first
        $pdf = Pdf::view('certificate.new-student-template', ['processedData' => $data])
            ->paperSize(315, 392) // 1192x1482px converted to mm
            ->margins(0, 0, 0, 0);

        // Save PDF temporarily
        $tempPdfPath = storage_path('app/temp_' . uniqid() . '.pdf');
        $pdf->save($tempPdfPath);

        // Step 2: Convert PDF to JPG with high DPI quality
        $this->convertPdfToJpg($tempPdfPath, $filePathJpg);
    }
}
