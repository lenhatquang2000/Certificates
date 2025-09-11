@extends('layouts.main')

@section('title', 'Create Certificates with Excel | PiSystem')

@section('content')
<div class="certificate-create container">
    <h2>Bulk Certificate Creation / Tạo nhiều giấy khen từ Excel</h2>
    <p>
        Tải lên file Excel (.xlsx, .xls) với các cột dữ liệu (cột đầu tiên là <strong>msvv</strong>):<br>
        <strong>
            msvv, recipient_name, award_title, academic_year, award_title_english, program, program_english, issue_day, issue_month, issue_year, location, decision_prefix, rector_name
        </strong>
    </p>
    <form method="POST" action="{{ route('certificate.generate.bulk') }}" enctype="multipart/form-data" id="bulkCertificateForm">
        @csrf

        <!-- Excel File Upload -->
        <div class="form-group mb-3">
            <label for="excel_file" class="form-label">
                <strong>Excel File / Tệp Excel <span class="text-danger">*</span></strong>
            </label>
            <input type="file" id="excel_file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
            <small class="form-text text-muted">
                File Excel phải có các cột: <strong>msvv</strong>, recipient_name, award_title, academic_year, award_title_english, program, program_english, issue_day, issue_month, issue_year, location, decision_prefix, rector_name
            </small>
            <a href="{{ asset('sample/certificate_bulk_template.xlsx') }}" class="btn btn-link mt-2" download>
                <i class="fas fa-download"></i> Tải file mẫu
            </a>
        </div>

        <!-- Default Values (optional, sẽ dùng nếu cột trong excel bị thiếu hoặc để trống) -->
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

        <!-- Submit Buttons -->
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-file-pdf"></i> Generate PDF Certificates / Tạo giấy khen PDF hàng loạt
            </button>
            <button type="button" class="btn btn-success btn-lg" onclick="generateBulkJPG()">
                <i class="fas fa-image"></i> Generate JPG Certificates / Tạo giấy khen JPG hàng loạt
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
    console.log("Bulk Certificate page loaded");

    // Optional: Auto-fill English program if user enters default_program
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

    // Optional: Auto-fill English award title if user selects default_award_title or academic year
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

    // Validate file input before submit
    document.getElementById('bulkCertificateForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('excel_file');
        if (!fileInput.value) {
            e.preventDefault();
            alert('Vui lòng chọn file Excel!');
            fileInput.style.borderColor = 'red';
        } else {
            fileInput.style.borderColor = '';
        }
    });

    function generateBulkJPG() {
        const form = document.getElementById('bulkCertificateForm');
        const fileInput = document.getElementById('excel_file');
        
        if (!fileInput.value) {
            alert('Vui lòng chọn file Excel!');
            fileInput.style.borderColor = 'red';
            return;
        }
        
        fileInput.style.borderColor = '';
        form.action = "{{ route('certificate.generate.bulk.jpg') }}";
        form.submit();
    }
</script>
@endpush