# Hướng dẫn cài đặt chức năng xuất JPG

## Tổng quan
Dự án đã được cập nhật để hỗ trợ xuất giấy khen dưới dạng file JPG thay vì chỉ PDF. Chức năng này sử dụng thư viện Imagick để chuyển đổi từ PDF sang JPG.

## Các thay đổi đã thực hiện

### 1. Controller (RecipientController.php)
- ✅ Thêm method `generateJpg()` - xuất 1 file JPG
- ✅ Thêm method `generateBulkJpg()` - xuất nhiều file JPG từ Excel
- ✅ Thêm method `generateJpgs()` - helper method để tạo nhiều JPG
- ✅ Thêm method `generateSingleJpg()` - helper method để tạo 1 JPG
- ✅ Cập nhật method `generateFilename()` để hỗ trợ extension JPG

### 2. Routes (web.php)
- ✅ Thêm route `POST /certificate/generate-jpg` cho xuất JPG đơn lẻ
- ✅ Thêm route `POST /create-certificate-bulk-jpg` cho xuất JPG hàng loạt

### 3. Views
- ✅ Thêm button "Generate JPG" vào form tạo giấy khen đơn lẻ (create.blade.php)
- ✅ Thêm button "Generate JPG Certificates" vào form tạo giấy khen hàng loạt (createwithexcel.blade.php)
- ✅ Thêm JavaScript functions để xử lý xuất JPG

### 4. Dependencies (composer.json)
- ✅ Thêm thư viện `imagick/imagick: ^3.7`

## Cài đặt thư viện cần thiết

### Bước 1: Cài đặt ImageMagick
Trước khi cài đặt PHP extension Imagick, bạn cần cài đặt ImageMagick:

#### Trên Windows:
1. Tải ImageMagick từ: https://imagemagick.org/script/download.php#windows
2. Cài đặt ImageMagick
3. Thêm đường dẫn ImageMagick vào PATH environment variable

#### Trên Ubuntu/Debian:
```bash
sudo apt-get update
sudo apt-get install imagemagick libmagickwand-dev
```

#### Trên CentOS/RHEL:
```bash
sudo yum install ImageMagick ImageMagick-devel
```

### Bước 2: Cài đặt PHP Imagick extension
```bash
# Trên Ubuntu/Debian
sudo apt-get install php-imagick

# Trên CentOS/RHEL
sudo yum install php-imagick

# Hoặc sử dụng PECL
sudo pecl install imagick
```

### Bước 3: Cài đặt Composer dependencies
```bash
composer install
```

### Bước 4: Kiểm tra cài đặt
Tạo file test để kiểm tra Imagick:
```php
<?php
if (extension_loaded('imagick')) {
    echo "Imagick extension is loaded successfully!";
} else {
    echo "Imagick extension is NOT loaded!";
}
?>
```

## Cách sử dụng

### Xuất JPG đơn lẻ:
1. Truy cập trang tạo giấy khen
2. Điền thông tin
3. Nhấn button "Generate JPG / Tạo giấy khen JPG"
4. File JPG sẽ được tải xuống

### Xuất JPG hàng loạt từ Excel:
1. Truy cập trang tạo giấy khen hàng loạt
2. Upload file Excel
3. Nhấn button "Generate JPG Certificates / Tạo giấy khen JPG hàng loạt"
4. File ZIP chứa các file JPG sẽ được tải xuống

## Lưu ý kỹ thuật

### Chất lượng JPG:
- Độ phân giải: 300 DPI
- Chất lượng nén: 95%
- Format: JPEG

### Quy trình chuyển đổi:
1. HTML template → PDF (sử dụng Spatie PDF)
2. PDF → JPG (sử dụng Imagick)
3. Trả về file JPG hoặc ZIP chứa nhiều JPG

### Xử lý lỗi:
- Tất cả lỗi được log vào Laravel log
- Hiển thị thông báo lỗi thân thiện cho người dùng
- Tự động dọn dẹp file tạm thời

## Troubleshooting

### Lỗi "Class 'Imagick' not found":
- Kiểm tra PHP extension Imagick đã được cài đặt
- Restart web server sau khi cài đặt extension
- Kiểm tra php.ini có load extension imagick

### Lỗi "ImageMagick not found":
- Cài đặt ImageMagick system library
- Kiểm tra PATH environment variable
- Restart web server

### Lỗi "Permission denied":
- Kiểm tra quyền ghi file trong thư mục storage/app
- Đảm bảo web server có quyền tạo file tạm thời

## Performance
- JPG generation chậm hơn PDF generation do cần chuyển đổi thêm 1 bước
- Với file hàng loạt, có thể mất thời gian lâu hơn
- Khuyến nghị sử dụng queue cho việc xử lý hàng loạt lớn
