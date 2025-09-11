@extends('layouts.main')

@section('title', 'Create Certificate | PiSystem')

@section('content')
<div class="certificate-create container">
    <h2>Create Certificate / Tạo Giấy Khen</h2>
    <p>Chọn cách tạo giấy khen: nhập thông tin trực tiếp hoặc tải lên file Excel.</p>
    
    <!-- Background Preview Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-image"></i> Ảnh nền hiện tại / Current Background</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Template 1 (Đang sử dụng / Currently in use)</h6>
                            <img src="{{ asset('assets/template/certificate_template.jpg') }}" 
                                 alt="Certificate Template 1" 
                                 class="img-fluid border rounded" 
                                 style="max-height: 300px; width: auto;">
                            <p class="mt-2"><small class="text-muted">certificate_template.jpg</small></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Template 2 (Có sẵn / Available)</h6>
                            <img src="{{ asset('assets/template/certificate_template2.jpg') }}" 
                                 alt="Certificate Template 2" 
                                 class="img-fluid border rounded" 
                                 style="max-height: 300px; width: auto;">
                            <p class="mt-2"><small class="text-muted">certificate_template2.jpg</small></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Chữ ký / Signature</h6>
                            <img src="{{ asset('assets/template/sign.png') }}" 
                                 alt="Signature" 
                                 class="img-fluid border rounded" 
                                 style="max-height: 150px; width: auto; background: white;">
                            <p class="mt-2"><small class="text-muted">sign.png</small></p>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>Thông tin ảnh nền:</strong><br>
                                • <strong>Ảnh nền chính:</strong> <code>certificate_template.jpg</code><br>
                                • <strong>Chữ ký:</strong> <code>sign.png</code><br>
                                • <strong>Kích thước:</strong> 842x595px (A4 landscape)<br>
                                • <strong>Định dạng:</strong> JPG cho nền, PNG cho chữ ký
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-warning">
                            <strong>Lưu ý:</strong> Hiện tại hệ thống đang sử dụng <code>certificate_template.jpg</code> làm ảnh nền mặc định. 
                            Nếu bạn muốn thay đổi ảnh nền, hãy thay thế file này hoặc cập nhật code trong file <code>pdf-template-pre.blade.php</code>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mode Selection -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Tạo lẻ / Single Certificate</h5>
                    <p class="card-text">Nhập thông tin trực tiếp để tạo 1 giấy khen</p>
                    <button type="button" class="btn btn-outline-primary" onclick="switchMode('single')">
                        <i class="fas fa-edit"></i> Nhập thông tin
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Tạo nhiều / Bulk Certificates</h5>
                    <p class="card-text">Tải lên file Excel để tạo nhiều giấy khen</p>
                    <button type="button" class="btn btn-outline-success" onclick="switchMode('bulk')">
                        <i class="fas fa-file-excel"></i> Tải Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('certificate.generate.pdf') }}" id="certificateForm" enctype="multipart/form-data">
        @csrf
        
        <!-- Single Certificate Mode -->
        <div id="singleMode" class="certificate-mode">
            <h4><i class="fas fa-edit"></i> Thông tin giấy khen</h4>
            
            <!-- Recipient Name -->
            <div class="form-group mb-3">
                <label for="recipient_name" class="form-label">
                    <strong>Recipient Name / Tên người nhận <span class="text-danger">*</span></strong>
                </label>
                <input type="text" id="recipient_name" name="recipient_name"
                       class="form-control" placeholder="Ví dụ: NGUYỄN VĂN A" 
                       value="{{ old('recipient_name') }}">
                <small class="form-text text-muted">Tên sẽ được hiển thị IN HOA trên giấy khen</small>
            </div>
            
            <!-- Award Title -->
            <div class="form-group mb-3">
                <label for="award_title" class="form-label">
                    <strong>Award Title / Danh hiệu <span class="text-danger">*</span></strong>
                </label>
                <select id="award_title" name="award_title" class="form-control">
                    <option value="">-- Chọn danh hiệu --</option>
                    <option value="Sinh viên Xuất sắc năm học" {{ old('award_title') == 'Sinh viên Xuất sắc năm học' ? 'selected' : '' }}>
                        Sinh viên Xuất sắc năm học
                    </option>
                    <option value="Sinh viên Giỏi năm học" {{ old('award_title') == 'Sinh viên Giỏi năm học' ? 'selected' : '' }}>
                        Sinh viên Giỏi năm học
                    </option>
                    <option value="Sinh viên Tiên tiến năm học" {{ old('award_title') == 'Sinh viên Tiên tiến năm học' ? 'selected' : '' }}>
                        Sinh viên Tiên tiến năm học
                    </option>
                </select>
            </div>

            <!-- Academic Year -->
            <div class="form-group mb-3">
                <label for="academic_year" class="form-label">
                    <strong>Academic Year / Năm học <span class="text-danger">*</span></strong>
                </label>
                <input type="text" id="academic_year" name="academic_year"
                       class="form-control" placeholder="Ví dụ: 2024 – 2025" 
                       value="{{ old('academic_year', '2024 – 2025') }}">
            </div>

            <!-- Award Title English -->
            <div class="form-group mb-3">
                <label for="award_title_english" class="form-label">
                    <strong>Award Title (English) / Danh hiệu (Tiếng Anh) <span class="text-danger">*</span></strong>
                </label>
                <input type="text" id="award_title_english" name="award_title_english"
                       class="form-control" placeholder="Ví dụ: Excellent Student of The Class, Academic Year 2024 – 2025" 
                       value="{{ old('award_title_english') }}">
                <small class="form-text text-muted">Bản dịch tiếng Anh của danh hiệu</small>
            </div>
            
            <!-- Class/Program -->
            <div class="form-group mb-3">
                <label for="program" class="form-label">
                    <strong>Class/Program / Lớp học <span class="text-danger">*</span></strong>
                </label>
                <input type="text" id="program" name="program"
                       class="form-control" placeholder="Ví dụ: TÀI CHÍNH - NGÂN HÀNG KHÓA 17/2024" 
                       value="{{ old('program') }}">
                <small class="form-text text-muted">Thông tin lớp học hoặc chương trình</small>
            </div>

            <!-- Program English -->
            <div class="form-group mb-3">
                <label for="program_english" class="form-label">
                    <strong>Class/Program (English) / Lớp học (Tiếng Anh)</strong>
                </label>
                <input type="text" id="program_english" name="program_english"
                       class="form-control" placeholder="Ví dụ: Finance - Banking K17/2024" 
                       value="{{ old('program_english') }}">
                <small class="form-text text-muted">Bản dịch tiếng Anh của tên lớp (tùy chọn)</small>
            </div>
            
            <!-- Issue Date -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="issue_day" class="form-label"><strong>Day / Ngày <span class="text-danger">*</span></strong></label>
                        <input type="number" id="issue_day" name="issue_day" 
                               class="form-control" min="1" max="31" placeholder="18"
                               value="{{ old('issue_day', date('j')) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="issue_month" class="form-label"><strong>Month / Tháng <span class="text-danger">*</span></strong></label>
                        <input type="number" id="issue_month" name="issue_month" 
                               class="form-control" min="1" max="12" placeholder="12"
                               value="{{ old('issue_month', date('n')) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="issue_year" class="form-label"><strong>Year / Năm <span class="text-danger">*</span></strong></label>
                        <input type="number" id="issue_year" name="issue_year" 
                               class="form-control" min="2020" max="2030" placeholder="2024"
                               value="{{ old('issue_year', date('Y')) }}">
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="form-group mb-3">
                <label for="location" class="form-label">
                    <strong>Location / Địa điểm <span class="text-danger">*</span></strong>
                </label>
                <input type="text" id="location" name="location"
                       class="form-control" placeholder="Hậu Giang" 
                       value="{{ old('location', 'Hậu Giang') }}">
            </div>

            <!-- Decision Number Prefix -->
            <div class="form-group mb-3">
                <label for="decision_prefix" class="form-label">
                    <strong>Decision Number Prefix / Tiền tố số quyết định</strong>
                </label>
                <input type="text" id="decision_prefix" name="decision_prefix"
                       class="form-control" placeholder="Để trống sẽ tự động tạo số ngẫu nhiên" 
                       value="{{ old('decision_prefix') }}">
                <small class="form-text text-muted">Nếu không nhập, hệ thống sẽ tự động tạo số ngẫu nhiên</small>
            </div>

            <!-- Decision Number Prefix English -->
            <div class="form-group mb-3">
                <label for="decision_prefix_english" class="form-label">
                    <strong>Decision Number Prefix (English) / Tiền tố số quyết định (Tiếng Anh)</strong>
                </label>
                <input type="text" id="decision_prefix_english" name="decision_prefix_english"
                       class="form-control" placeholder="Để trống sẽ tự động tạo số ngẫu nhiên" 
                       value="{{ old('decision_prefix_english') }}">
                <small class="form-text text-muted">Bản dịch tiếng Anh của tiền tố số quyết định (tùy chọn)</small>
            </div>

            <!-- Rector Name -->
            <div class="form-group mb-3">
                <label for="rector_name" class="form-label">
                    <strong>Rector Name / Tên Hiệu trưởng <span class="text-danger">*</span></strong>
                </label>
                <input type="text" id="rector_name" name="rector_name"
                       class="form-control" placeholder="Dương Đăng Khoa" 
                       value="{{ old('rector_name', 'Dương Đăng Khoa') }}">
            </div>
        </div>

        <!-- Bulk Certificate Mode -->
        <div id="bulkMode" class="certificate-mode" style="display: none;">
            <h4><i class="fas fa-file-excel"></i> Tạo nhiều giấy khen từ Excel</h4>
            <p>
                Tải lên file Excel (.xlsx, .xls) với các cột dữ liệu (cột đầu tiên là <strong>mssv</strong>):<br>
                <strong>
                    mssv, recipient_name, award_title, academic_year, award_title_english, program, program_english, issue_day, issue_month, issue_year, location, decision_prefix, rector_name
                </strong>
            </p>

            <!-- Excel File Upload -->
            <div class="form-group mb-3">
                <label for="excel_file" class="form-label">
                    <strong>Excel File / Tệp Excel <span class="text-danger">*</span></strong>
                </label>
                <input type="file" id="excel_file" name="excel_file" class="form-control" accept=".xlsx,.xls">
                <small class="form-text text-muted">
                    File Excel phải có các cột: <strong>mssv</strong>, recipient_name, award_title, academic_year, award_title_english, program, program_english, issue_day, issue_month, issue_year, location, decision_prefix, rector_name
                </small>
                <a href="{{ asset('sample/certificate_bulk_template.xlsx') }}" class="btn btn-link mt-2" download>
                    <i class="fas fa-download"></i> Tải file mẫu
                </a>
            </div>

            <!-- Default Values (optional) -->
            <div class="alert alert-info">
                <strong>Tùy chọn:</strong> Bạn có thể nhập giá trị mặc định cho các trường nếu file Excel không có hoặc để trống.<br>
                Các trường này sẽ được dùng cho tất cả các dòng nếu không có dữ liệu trong file.
            </div>
            <div class="row">
                <div class="col-md-6">
                    <!-- Award Title -->
                    <div class="form-group mb-3">
                        <label for="default_award_title" class="form-label">
                            <strong>Award Title / Danh hiệu (mặc định)</strong>
                        </label>
                        <select id="default_award_title" name="default_award_title" class="form-control">
                            <option value="">-- Không chọn --</option>
                            <option value="Sinh viên Xuất sắc năm học">Sinh viên Xuất sắc năm học</option>
                            <option value="Sinh viên Giỏi năm học">Sinh viên Giỏi năm học</option>
                            <option value="Sinh viên Tiên tiến năm học">Sinh viên Tiên tiến năm học</option>
                        </select>
                    </div>
                    <!-- Academic Year -->
                    <div class="form-group mb-3">
                        <label for="default_academic_year" class="form-label">
                            <strong>Academic Year / Năm học (mặc định)</strong>
                        </label>
                        <input type="text" id="default_academic_year" name="default_academic_year"
                            class="form-control" placeholder="Ví dụ: 2024 – 2025">
                    </div>
                    <!-- Award Title English -->
                    <div class="form-group mb-3">
                        <label for="default_award_title_english" class="form-label">
                            <strong>Award Title (English) / Danh hiệu (Tiếng Anh) (mặc định)</strong>
                        </label>
                        <input type="text" id="default_award_title_english" name="default_award_title_english"
                            class="form-control" placeholder="Ví dụ: Excellent Student of The Class, Academic Year 2024 – 2025">
                    </div>
                    <!-- Program -->
                    <div class="form-group mb-3">
                        <label for="default_program" class="form-label">
                            <strong>Class/Program / Lớp học (mặc định)</strong>
                        </label>
                        <input type="text" id="default_program" name="default_program"
                            class="form-control" placeholder="Ví dụ: TÀI CHÍNH - NGÂN HÀNG KHÓA 17/2024">
                    </div>
                    <!-- Program English -->
                    <div class="form-group mb-3">
                        <label for="default_program_english" class="form-label">
                            <strong>Class/Program (English) / Lớp học (Tiếng Anh) (mặc định)</strong>
                        </label>
                        <input type="text" id="default_program_english" name="default_program_english"
                            class="form-control" placeholder="Ví dụ: Finance - Banking K17/2024">
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Issue Date -->
                    <div class="form-group mb-3">
                        <label class="form-label"><strong>Issue Date / Ngày cấp (mặc định)</strong></label>
                        <div class="row">
                            <div class="col-4">
                                <input type="number" id="default_issue_day" name="default_issue_day"
                                    class="form-control" min="1" max="31" placeholder="Ngày">
                            </div>
                            <div class="col-4">
                                <input type="number" id="default_issue_month" name="default_issue_month"
                                    class="form-control" min="1" max="12" placeholder="Tháng">
                            </div>
                            <div class="col-4">
                                <input type="number" id="default_issue_year" name="default_issue_year"
                                    class="form-control" min="2020" max="2030" placeholder="Năm">
                            </div>
                        </div>
                    </div>
                    <!-- Location -->
                    <div class="form-group mb-3">
                        <label for="default_location" class="form-label">
                            <strong>Location / Địa điểm (mặc định)</strong>
                        </label>
                        <input type="text" id="default_location" name="default_location"
                            class="form-control" placeholder="Hậu Giang">
                    </div>
                    <!-- Decision Number Prefix -->
                    <div class="form-group mb-3">
                        <label for="default_decision_prefix" class="form-label">
                            <strong>Decision Number Prefix / Tiền tố số quyết định (mặc định)</strong>
                        </label>
                        <input type="text" id="default_decision_prefix" name="default_decision_prefix"
                            class="form-control" placeholder="Để trống sẽ tự động tạo số ngẫu nhiên">
                    </div>
                    <!-- Decision Number Prefix English -->
                    <div class="form-group mb-3">
                        <label for="default_decision_prefix_english" class="form-label">
                            <strong>Decision Number Prefix (English) / Tiền tố số quyết định (Tiếng Anh) (mặc định)</strong>
                        </label>
                        <input type="text" id="default_decision_prefix_english" name="default_decision_prefix_english"
                            class="form-control" placeholder="Để trống sẽ tự động tạo số ngẫu nhiên">
                    </div>
                    <!-- Rector Name -->
                    <div class="form-group mb-3">
                        <label for="default_rector_name" class="form-label">
                            <strong>Rector Name / Tên Hiệu trưởng (mặc định)</strong>
                        </label>
                        <input type="text" id="default_rector_name" name="default_rector_name"
                            class="form-control" placeholder="Dương Đăng Khoa">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Submit Buttons -->
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <i class="fas fa-file-pdf"></i> Generate PDF / Tạo giấy khen PDF
            </button>
            <button type="button" class="btn btn-success btn-lg" onclick="generateJPG()">
                <i class="fas fa-image"></i> Generate JPG / Tạo giấy khen JPG
            </button>
            <button type="button" class="btn btn-secondary btn-lg" onclick="previewPDF()" id="previewBtn">
                <i class="fas fa-eye"></i> Preview and Adjust / Xem trước và điều chỉnh
            </button>
        </div>
                
        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
    let currentMode = 'single'; // Default mode
    
    function switchMode(mode) {
        currentMode = mode;
        
        // Hide all modes
        document.querySelectorAll('.certificate-mode').forEach(el => {
            el.style.display = 'none';
        });
        
        // Show selected mode
        document.getElementById(mode + 'Mode').style.display = 'block';
        
        // Update button text and form action
        const submitBtn = document.getElementById('submitBtn');
        const previewBtn = document.getElementById('previewBtn');
        
        if (mode === 'single') {
            submitBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Generate PDF / Tạo giấy khen PDF';
            previewBtn.style.display = 'inline-block';
        } else {
            submitBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Generate PDF Certificates / Tạo giấy khen PDF hàng loạt';
            previewBtn.style.display = 'none';
        }
    }
    
    // Auto-fill functions for single mode
    document.getElementById('program').addEventListener('input', function() {
        const vietnameseProgram = this.value;
        const englishProgramField = document.getElementById('program_english');
        
        if (vietnameseProgram && !englishProgramField.value) {
            let englishProgram = vietnameseProgram
                .replace(/TÀI CHÍNH - NGÂN HÀNG/gi, 'Finance - Banking')
                .replace(/KHÓA/gi, 'K')
                .replace(/LỚP/gi, 'Class');
            
            englishProgramField.value = englishProgram;
        }
    });

    document.getElementById('award_title').addEventListener('change', function() {
        const vietnameseTitle = this.value;
        const englishTitleField = document.getElementById('award_title_english');
        const academicYear = document.getElementById('academic_year').value || '2024 – 2025';
        
        let englishTitle = '';
        switch(vietnameseTitle) {
            case 'Sinh viên Xuất sắc năm học':
                englishTitle = `Excellent Student of The Class, Academic Year ${academicYear}`;
                break;
            case 'Sinh viên Giỏi năm học':
                englishTitle = `Good Student of The Class, Academic Year ${academicYear}`;
                break;
            case 'Sinh viên Tiên tiến năm học':
                englishTitle = `Advanced Student of The Class, Academic Year ${academicYear}`;
                break;
        }
        
        englishTitleField.value = englishTitle;
    });

    document.getElementById('academic_year').addEventListener('input', function() {
        const academicYear = this.value;
        const awardTitle = document.getElementById('award_title').value;
        const englishTitleField = document.getElementById('award_title_english');
        
        if (awardTitle && academicYear) {
            let englishTitle = englishTitleField.value;
            englishTitle = englishTitle.replace(/Academic Year \d{4} – \d{4}/, `Academic Year ${academicYear}`);
            englishTitleField.value = englishTitle;
        }
    });
    
    // Auto-fill functions for bulk mode
    document.getElementById('default_program')?.addEventListener('input', function() {
        const vietnameseProgram = this.value;
        const englishProgramField = document.getElementById('default_program_english');
        if (vietnameseProgram && !englishProgramField.value) {
            let englishProgram = vietnameseProgram
                .replace(/TÀI CHÍNH - NGÂN HÀNG/gi, 'Finance - Banking')
                .replace(/KHÓA/gi, 'K')
                .replace(/LỚP/gi, 'Class');
            englishProgramField.value = englishProgram;
        }
    });

    document.getElementById('default_award_title')?.addEventListener('change', function() {
        const vietnameseTitle = this.value;
        const englishTitleField = document.getElementById('default_award_title_english');
        const academicYear = document.getElementById('default_academic_year').value || '2024 – 2025';
        let englishTitle = '';
        switch(vietnameseTitle) {
            case 'Sinh viên Xuất sắc năm học':
                englishTitle = `Excellent Student of The Class, Academic Year ${academicYear}`;
                break;
            case 'Sinh viên Giỏi năm học':
                englishTitle = `Good Student of The Class, Academic Year ${academicYear}`;
                break;
            case 'Sinh viên Tiên tiến năm học':
                englishTitle = `Advanced Student of The Class, Academic Year ${academicYear}`;
                break;
        }
        englishTitleField.value = englishTitle;
    });

    document.getElementById('default_academic_year')?.addEventListener('input', function() {
        const academicYear = this.value;
        const awardTitle = document.getElementById('default_award_title').value;
        const englishTitleField = document.getElementById('default_award_title_english');
        if (awardTitle && academicYear) {
            let englishTitle = englishTitleField.value;
            englishTitle = englishTitle.replace(/Academic Year \d{4} – \d{4}/, `Academic Year ${academicYear}`);
            englishTitleField.value = englishTitle;
        }
    });
    
    // Form validation and submission
    document.querySelector('form').addEventListener('submit', function(e) {
        if (currentMode === 'single') {
            // Validate single mode
            const requiredFields = ['recipient_name', 'award_title', 'academic_year', 'award_title_english', 'program', 'issue_day', 'issue_month', 'issue_year', 'location', 'rector_name'];
            let isValid = true;
            let missingFields = [];
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = 'red';
                    missingFields.push(input.previousElementSibling.textContent.replace(' *', ''));
                } else {
                    input.style.borderColor = '';
                }
            });
            
            const day = parseInt(document.getElementById('issue_day').value);
            const month = parseInt(document.getElementById('issue_month').value);
            const year = parseInt(document.getElementById('issue_year').value);
            
            if (day < 1 || day > 31 || month < 1 || month > 12 || year < 2020) {
                isValid = false;
                alert('Please enter a valid date / Vui lòng nhập ngày hợp lệ');
            }
            
            if (!isValid) {
                e.preventDefault();
                if (missingFields.length > 0) {
                    alert('Please fill in all required fields: \n' + missingFields.join('\n') + 
                          '\n\nVui lòng điền đầy đủ các trường bắt buộc');
                }
            }
        } else {
            // Validate bulk mode
            const fileInput = document.getElementById('excel_file');
            if (!fileInput.value) {
                e.preventDefault();
                alert('Vui lòng chọn file Excel!');
                fileInput.style.borderColor = 'red';
            } else {
                fileInput.style.borderColor = '';
            }
        }
    });

    function previewPDF() {
        if (currentMode === 'single') {
            const form = document.getElementById('certificateForm');
            form.action = "{{ route('certificate.preview.pdf') }}";
            form.submit();
        }
    }

    function generateJPG() {
        const form = document.getElementById('certificateForm');
        form.action = "{{ route('certificate.generate.jpg') }}";
        form.submit();
    }
</script>
@endpush
