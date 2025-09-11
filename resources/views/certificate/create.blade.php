@extends('layouts.main')

@section('title', 'Create Certificate | PiSystem')

@section('content')
<div class="certificate-create container">
    <h2>Create New Certificate / Tạo Giấy Khen</h2>
    <p>Fill in the information below to generate a new certificate using the default template.</p>

    <form method="POST" action="{{ route('certificate.generate.pdf') }}" id="certificateForm">
        @csrf
        
        <!-- Recipient Name -->
        <div class="form-group mb-3">
            <label for="recipient_name" class="form-label">
                <strong>Recipient Name / Tên người nhận <span class="text-danger">*</span></strong>
            </label>
            <input type="text" id="recipient_name" name="recipient_name"
                   class="form-control" placeholder="Ví dụ: NGUYỄN VĂN A" 
                   value="{{ old('recipient_name') }}" required>
            <small class="form-text text-muted">Tên sẽ được hiển thị IN HOA trên giấy khen</small>
        </div>
        
        <!-- Award Title -->
        <div class="form-group mb-3">
            <label for="award_title" class="form-label">
                <strong>Award Title / Danh hiệu <span class="text-danger">*</span></strong>
            </label>
            <select id="award_title" name="award_title" class="form-control" required>
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
                   value="{{ old('academic_year', '2024 – 2025') }}" required>
        </div>

        <!-- Award Title English -->
        <div class="form-group mb-3">
            <label for="award_title_english" class="form-label">
                <strong>Award Title (English) / Danh hiệu (Tiếng Anh) <span class="text-danger">*</span></strong>
            </label>
            <input type="text" id="award_title_english" name="award_title_english"
                   class="form-control" placeholder="Ví dụ: Excellent Student of The Class, Academic Year 2024 – 2025" 
                   value="{{ old('award_title_english') }}" required>
            <small class="form-text text-muted">Bản dịch tiếng Anh của danh hiệu</small>
        </div>
        
        <!-- Class/Program -->
        <div class="form-group mb-3">
            <label for="program" class="form-label">
                <strong>Class/Program / Lớp học <span class="text-danger">*</span></strong>
            </label>
            <input type="text" id="program" name="program"
                   class="form-control" placeholder="Ví dụ: TÀI CHÍNH - NGÂN HÀNG KHÓA 17/2024" 
                   value="{{ old('program') }}" required>
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
                           value="{{ old('issue_day', date('j')) }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="issue_month" class="form-label"><strong>Month / Tháng <span class="text-danger">*</span></strong></label>
                    <input type="number" id="issue_month" name="issue_month" 
                           class="form-control" min="1" max="12" placeholder="12"
                           value="{{ old('issue_month', date('n')) }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="issue_year" class="form-label"><strong>Year / Năm <span class="text-danger">*</span></strong></label>
                    <input type="number" id="issue_year" name="issue_year" 
                           class="form-control" min="2020" max="2030" placeholder="2024"
                           value="{{ old('issue_year', date('Y')) }}" required>
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
                   value="{{ old('location', 'Hậu Giang') }}" required>
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

        <!-- Rector Name -->
        <div class="form-group mb-3">
            <label for="rector_name" class="form-label">
                <strong>Rector Name / Tên Hiệu trưởng <span class="text-danger">*</span></strong>
            </label>
            <input type="text" id="rector_name" name="rector_name"
                   class="form-control" placeholder="Dương Đăng Khoa" 
                   value="{{ old('rector_name', 'Dương Đăng Khoa') }}" required>
        </div>
        
        <!-- Submit Buttons -->
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-file-pdf"></i> Generate PDF / Tạo giấy khen PDF
            </button>
            <button type="button" class="btn btn-success btn-lg" onclick="generateJPG()">
                <i class="fas fa-image"></i> Generate JPG / Tạo giấy khen JPG
            </button>
            <button type="button" class="btn btn-secondary btn-lg" onclick="previewPDF()">
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
    console.log("Create Certificate page loaded");
    
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
    
    document.querySelector('form').addEventListener('submit', function(e) {
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
    });

    function previewPDF() {
        const form = document.getElementById('certificateForm');
        form.action = "{{ route('certificate.preview.pdf') }}";
        form.submit();
    }

    function generateJPG() {
        const form = document.getElementById('certificateForm');
        form.action = "{{ route('certificate.generate.jpg') }}";
        form.submit();
    }
</script>
@endpush