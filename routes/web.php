<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ExcelController;

Route::get('/test-pdf', function () {
    return \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML('<h1>Hello PDF</h1>')
        ->inline('test.pdf');
});

Route::get('/system-status', [RecipientController::class, 'checkSystemStatus'])->name('system.status');

Route::get('/test-imagick', function () {
    $html = '<h1>PHP Imagick Test</h1><hr>';
    
    $html .= '<h2>PHP Information</h2>';
    $html .= '<strong>PHP Version:</strong> ' . phpversion() . '<br>';
    $html .= '<strong>PHP SAPI:</strong> ' . php_sapi_name() . '<br>';
    $html .= '<strong>Server:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '<br><br>';
    
    $html .= '<h2>Imagick Extension Check</h2>';
    if (extension_loaded('imagick')) {
        $html .= '<span style="color: green; font-weight: bold;">✅ Imagick extension is loaded!</span><br><br>';
        
        try {
            $imagick = new Imagick();
            $version = $imagick->getVersion();
            $html .= '<strong>Imagick version:</strong> ' . $version['versionString'] . '<br>';
            
            $formats = $imagick->queryFormats();
            $html .= '<strong>Supported formats:</strong> ' . count($formats) . '<br><br>';
            
            $html .= '<h3>Format Support Check</h3>';
            if (in_array('PDF', $formats)) {
                $html .= '<span style="color: green;">✅ PDF format is supported</span><br>';
            } else {
                $html .= '<span style="color: red;">❌ PDF format is NOT supported</span><br>';
            }
            
            if (in_array('JPEG', $formats)) {
                $html .= '<span style="color: green;">✅ JPEG format is supported</span><br>';
            } else {
                $html .= '<span style="color: red;">❌ JPEG format is NOT supported</span><br>';
            }
            
            if (in_array('PNG', $formats)) {
                $html .= '<span style="color: green;">✅ PNG format is supported</span><br>';
            } else {
                $html .= '<span style="color: red;">❌ PNG format is NOT supported</span><br>';
            }
            
        } catch (Exception $e) {
            $html .= '<span style="color: red;">❌ Error creating Imagick object: ' . $e->getMessage() . '</span><br>';
        }
        
    } else {
        $html .= '<span style="color: red; font-weight: bold;">❌ Imagick extension is NOT loaded!</span><br><br>';
        
        $html .= '<h3>Available Extensions</h3>';
        $extensions = get_loaded_extensions();
        sort($extensions);
        $html .= '<div style="max-height: 200px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">';
        foreach ($extensions as $ext) {
            $html .= '- ' . $ext . '<br>';
        }
        $html .= '</div>';
    }
    
    $html .= '<br><h2>ImageMagick System Library Check</h2>';
    $output = [];
    $return_var = 0;
    exec('magick -version 2>&1', $output, $return_var);
    
    if ($return_var === 0) {
        $html .= '<span style="color: green; font-weight: bold;">✅ ImageMagick system library is available:</span><br>';
        $html .= '<pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd;">';
        $html .= implode("\n", array_slice($output, 0, 5));
        $html .= '</pre>';
    } else {
        $html .= '<span style="color: red; font-weight: bold;">❌ ImageMagick system library is NOT available</span><br>';
        $html .= '<strong>Error:</strong> ' . implode('<br>', $output) . '<br>';
    }
    
    $html .= '<br><h2>Test Simple Image Creation</h2>';
    if (extension_loaded('imagick')) {
        try {
            $imagick = new Imagick();
            $imagick->newImage(100, 100, 'white');
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);
            
            $html .= '<span style="color: green;">✅ Successfully created test image</span><br>';
            $html .= '<strong>Image size:</strong> ' . $imagick->getImageWidth() . 'x' . $imagick->getImageHeight() . '<br>';
            $html .= '<strong>Image format:</strong> ' . $imagick->getImageFormat() . '<br>';
            
        } catch (Exception $e) {
            $html .= '<span style="color: red;">❌ Error creating test image: ' . $e->getMessage() . '</span><br>';
        }
    } else {
        $html .= '<span style="color: red;">❌ Cannot test image creation - Imagick not loaded</span><br>';
    }
    
    return $html;
});

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::prefix('certificate')->name('certificate.')->group(function () {
    Route::get('/', [RecipientController::class, 'create'])->name('create');
    Route::get('/bulk', [RecipientController::class, 'bulk'])->name('bulk');
    Route::post('/preview', [RecipientController::class, 'preview'])->name('preview');
    Route::post('/preview-pdf', [RecipientController::class, 'previewPdf'])->name('preview.pdf');
    Route::post('/generate-pdf', [RecipientController::class, 'generateUnified'])->name('generate.pdf');
    Route::post('/generate-jpg', [RecipientController::class, 'generateUnifiedJpg'])->name('generate.jpg');
    Route::post('/preview-ajax', [RecipientController::class, 'previewAjax'])->name('preview.ajax');
});

// New Doctor Certificate Routes
Route::prefix('new-student')->name('new-student.')->group(function () {
    Route::get('/', [RecipientController::class, 'newStudentCreate'])->name('create');
    Route::post('/preview-pdf', [RecipientController::class, 'newStudentPreviewPdf'])->name('preview.pdf');
    Route::post('/generate-pdf', [RecipientController::class, 'newStudentGeneratePdf'])->name('generate.pdf');
    Route::post('/generate-jpg', [RecipientController::class, 'newStudentGenerateUnified'])->name('generate.jpg');
});

Route::get('/create-certificate', [RecipientController::class, 'create'])->name('create.certificate');
Route::post('/create-certificate-bulk', [RecipientController::class, 'generateBulkPdf'])->name('certificate.generate.bulk');
Route::post('/create-certificate-bulk-jpg', [RecipientController::class, 'generateBulkJpg'])->name('certificate.generate.bulk.jpg');
Route::get('/preview-certificate', [RecipientController::class, 'previewPdf'])->name('preview.certificate');
Route::post('/pdf/add-background', [PdfController::class, 'addBackground'])->name('pdf.add-background');
Route::get('/pdf/upload', [PdfController::class, 'showUploadForm'])->name('pdf.upload.form');

// Excel file check routes
Route::get('/check-excel', [ExcelController::class, 'checkExcelFile'])->name('excel.check');
Route::get('/api/check-excel', [ExcelController::class, 'checkExcelFileApi'])->name('excel.check.api');
