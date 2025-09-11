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
                    <form id="newStudentForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Student Name Input -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="student_name" class="form-label">
                                    <strong>Student Name / Tên Sinh Viên</strong>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="student_name" 
                                       name="student_name" 
                                       placeholder="Enter student name / Nhập tên sinh viên"
                                       required>
                                <div class="form-text">Enter the full name of the student / Nhập họ tên đầy đủ của sinh viên</div>
                            </div>
                        </div>

                        <!-- Background Image Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">
                                    <strong>Background Template / Chọn Ảnh Nền</strong>
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

                        <!-- Preview Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-eye"></i> Template Preview / Xem Trước Mẫu</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Available Templates / Mẫu Có Sẵn:</h6>
                                                <ul class="list-unstyled">
                                                    @foreach($backgroundImages as $image)
                                                    <li><i class="fas fa-image text-primary me-2"></i>{{ $image['display_name'] }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="alert alert-info">
                                                    <strong>Template Information / Thông Tin Mẫu:</strong><br>
                                                    • <strong>Size:</strong> 1192x1482px<br>
                                                    • <strong>Format:</strong> JPG<br>
                                                    • <strong>Location:</strong> <code>assets/newDoctorTemplate/</code><br>
                                                    • <strong>Output:</strong> PDF & JPG
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-info" onclick="previewCertificate()">
                                        <i class="fas fa-eye me-1"></i> Preview / Xem Trước
                                    </button>
                                    <button type="submit" formaction="{{ route('new-student.generate.pdf') }}" class="btn btn-primary">
                                        <i class="fas fa-file-pdf me-1"></i> Generate PDF
                                    </button>
                                    <button type="submit" formaction="{{ route('new-student.generate.jpg') }}" class="btn btn-success">
                                        <i class="fas fa-file-image me-1"></i> Generate JPG
                                    </button>
                                </div>
                            </div>
                        </div>
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

function previewCertificate() {
    const form = document.getElementById('newStudentForm');
    const formData = new FormData(form);
    
    // Create a temporary form for preview
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '{{ route("new-student.preview.pdf") }}';
    tempForm.target = '_blank';
    tempForm.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    tempForm.appendChild(csrfInput);
    
    // Add form data
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    }
    
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
}

// Auto-select first template on page load
document.addEventListener('DOMContentLoaded', function() {
    const firstCard = document.querySelector('.template-card');
    if (firstCard) {
        firstCard.classList.add('selected');
    }
});
</script>
@endsection
