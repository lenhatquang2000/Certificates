@extends('layouts.main')

@section('title', 'New Student Certificate | PiSystem')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-graduate"></i> New Student Certificate / Tạo Chứng Chỉ Tân Sinh Viên</h3>
                </div>
                <div class="card-body">
                    <!-- Mode Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Tạo lẻ / Single Certificate</h5>
                                    <p class="card-text">Nhập thông tin trực tiếp để tạo 1 chứng chỉ</p>
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
                                    <p class="card-text">Tải lên file Excel để tạo nhiều chứng chỉ</p>
                                    <button type="button" class="btn btn-outline-success" onclick="switchMode('bulk')">
                                        <i class="fas fa-file-excel"></i> Tải Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="newStudentForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Single Certificate Mode -->
                        <div id="singleMode" class="certificate-mode">
                            <h4><i class="fas fa-edit"></i> Thông tin chứng chỉ</h4>
                            
                            <!-- Student Name Input -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="student_name" class="form-label">
                                        <strong>Student Name / Tên Sinh Viên <span class="text-danger">*</span></strong>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="student_name" 
                                           name="student_name" 
                                           placeholder="Enter student name / Nhập tên sinh viên"
                                           value="{{ old('student_name') }}">
                                    <div class="form-text">Enter the full name of the student / Nhập họ tên đầy đủ của sinh viên</div>
                                </div>
                            </div>

                            <!-- Background Image Selection -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label">
                                        <strong>Background Template / Chọn Ảnh Nền <span class="text-danger">*</span></strong>
                                    </label>
                                    <div class="row">
                                        @foreach($backgroundImages as $index => $image)
                                        <div class="col-md-4 mb-3">
                                            <div class="card template-card" style="cursor: pointer;" onclick="selectTemplate('{{ $image['filename'] }}', this)">
                                                <div class="card-body text-center p-2">
                                                    <img src="{{ asset($image['path']) }}" 
                                                         alt="{{ $image['display_name'] }}"
                                                         class="img-fluid border rounded template-preview"
                                                         style="max-height: 200px; width: 100%; object-fit: cover;">
                                                    <div class="mt-2">
                                                        <small class="text-muted">{{ $image['display_name'] }}</small>
                                                    </div>
                                                    <input type="radio" 
                                                           name="background_image" 
                                                           value="{{ $image['filename'] }}" 
                                                           class="form-check-input template-radio" 
                                                           style="display: none;"
                                                           {{ $index === 0 ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text">Select a background template for the certificate / Chọn ảnh nền cho chứng chỉ</div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Certificate Mode -->
                        <div id="bulkMode" class="certificate-mode" style="display: none;">
                            <h4><i class="fas fa-file-excel"></i> Tạo nhiều chứng chỉ từ Excel</h4>
                            <p>
                                Tải lên file Excel (.xlsx, .xls) với các cột dữ liệu:<br>
                                <strong>MSSV, DANH HIỆU, HỌ VÀ TÊN</strong>
                            </p>

                            <!-- Excel File Upload -->
                            <div class="form-group mb-3">
                                <label for="excel_file" class="form-label">
                                    <strong>Excel File / Tệp Excel <span class="text-danger">*</span></strong>
                                </label>
                                <input type="file" id="excel_file" name="excel_file" class="form-control" accept=".xlsx,.xls">
                                <small class="form-text text-muted">
                                    File Excel phải có các cột: <strong>MSSV</strong>, DANH HIỆU, HỌ VÀ TÊN<br>
                                    Danh hiệu hợp lệ: TÂN BÁC SĨ, TÂN DƯỢC SĨ, TÂN CỬ NHÂN
                                </small>
                                <a href="{{ asset('sample_new_student.xlsx') }}" class="btn btn-link mt-2" download>
                                    <i class="fas fa-download"></i> Tải file mẫu
                                </a>
                            </div>

                            <div class="alert alert-info">
                                <strong>Thông tin:</strong><br>
                                • <strong>MSSV:</strong> Mã số sinh viên (dùng làm tên file)<br>
                                • <strong>DANH HIỆU:</strong> TÂN BÁC SĨ, TÂN DƯỢC SĨ, TÂN CỬ NHÂN<br>
                                • <strong>HỌ VÀ TÊN:</strong> Họ tên đầy đủ của sinh viên<br>
                                • <strong>Kết quả:</strong> File ZIP chứa các ảnh JPG
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-file-pdf"></i> Generate PDF / Tạo chứng chỉ PDF
                            </button>
                            <button type="button" class="btn btn-success btn-lg" onclick="generateJPG()">
                                <i class="fas fa-image"></i> Generate JPG / Tạo chứng chỉ JPG
                            </button>
                            <button type="button" class="btn btn-info btn-lg" onclick="previewPDF()" id="previewBtn">
                                <i class="fas fa-eye"></i> Preview / Xem trước
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
            </div>
        </div>
    </div>
</div>

<style>
.template-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.template-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.template-card.selected {
    border-color: #28a745;
    box-shadow: 0 4px 12px rgba(40,167,69,0.3);
}

.template-preview {
    transition: transform 0.3s ease;
}

.template-card:hover .template-preview {
    transform: scale(1.05);
}
</style>

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
        submitBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Generate PDF / Tạo chứng chỉ PDF';
        previewBtn.style.display = 'inline-block';
    } else {
        submitBtn.innerHTML = '<i class="fas fa-file-archive"></i> Generate Bulk JPG / Tạo chứng chỉ JPG hàng loạt';
        previewBtn.style.display = 'none';
    }
}

function selectTemplate(filename, element) {
    // Remove selected class from all cards
    document.querySelectorAll('.template-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    element.classList.add('selected');
    
    // Check the radio button
    const radio = element.querySelector('.template-radio');
    radio.checked = true;
}

function previewPDF() {
    if (currentMode === 'single') {
        const form = document.getElementById('newStudentForm');
        form.action = "{{ route('new-student.preview.pdf') }}";
        form.submit();
    }
}

function generateJPG() {
    const form = document.getElementById('newStudentForm');
    form.action = "{{ route('new-student.generate.jpg') }}";
    
    // Validate bulk mode
    if (currentMode === 'bulk') {
        const excelFile = document.getElementById('excel_file');
        
        if (!excelFile.files || excelFile.files.length === 0) {
            alert('Vui lòng chọn file Excel để tạo hàng loạt!\nPlease select an Excel file for bulk generation!');
            return false;
        }
        
        const file = excelFile.files[0];
        const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        
        if (!allowedTypes.includes(file.type)) {
            alert('Vui lòng chọn file Excel hợp lệ (.xlsx hoặc .xls)!\nPlease select a valid Excel file (.xlsx or .xls)!');
            return false;
        }
    }
    
    form.submit();
}

// Form validation and submission
document.querySelector('form').addEventListener('submit', function(e) {
    if (currentMode === 'single') {
        // Validate single mode
        const requiredFields = ['student_name', 'background_image'];
        let isValid = true;
        let missingFields = [];
        
        requiredFields.forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (!input || !input.value.trim()) {
                isValid = false;
                if (input) input.style.borderColor = 'red';
                missingFields.push(field === 'student_name' ? 'Tên sinh viên' : 'Ảnh nền');
            } else {
                if (input) input.style.borderColor = '';
            }
        });
        
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

// Auto-select first template on page load
document.addEventListener('DOMContentLoaded', function() {
    const firstCard = document.querySelector('.template-card');
    if (firstCard) {
        firstCard.classList.add('selected');
    }
});
</script>
@endsection
