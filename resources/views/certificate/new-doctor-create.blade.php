@extends('layouts.main')

@section('title', 'Create New Doctor Certificate | PiSystem')

@section('content')
<div class="certificate-create container">
    <h2>Create New Doctor Certificate / Tạo Chứng Chỉ Tân Bác Sĩ</h2>
    <p>Nhập tên bác sĩ để tạo chứng chỉ tân bác sĩ.</p>

    <!-- Background Preview Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user-md"></i> Mẫu Tân Bác Sĩ / New Doctor Template</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <img src="{{ asset('assets/newDoctorTemplate/TÂN BÁC SĨ.jpg') }}" 
                                 alt="New Doctor Template" 
                                 class="img-fluid border rounded" 
                                 style="max-height: 400px; width: auto;">
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <strong>Thông tin mẫu:</strong><br>
                                • <strong>Loại:</strong> Chứng chỉ Tân Bác Sĩ<br>
                                • <strong>Ảnh nền:</strong> <code>TÂN BÁC SĨ.jpg</code><br>
                                • <strong>Kích thước:</strong> 1192x1482px (Custom size)<br>
                                • <strong>Trường nhập:</strong> Chỉ cần tên bác sĩ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('new-doctor.generate.pdf') }}" id="newDoctorForm">
        @csrf
        
        <!-- Doctor Name -->
        <div class="form-group mb-4">
            <label for="doctor_name" class="form-label">
                <strong>Doctor Name / Tên Bác Sĩ <span class="text-danger">*</span></strong>
            </label>
            <input type="text" id="doctor_name" name="doctor_name"
                   class="form-control form-control-lg" 
                   placeholder="Ví dụ: NGUYỄN VĂN A" 
                   value="{{ old('doctor_name') }}" 
                   required
                   style="font-size: 18px; padding: 15px;">
            <small class="form-text text-muted">Tên sẽ được hiển thị IN HOA trên chứng chỉ</small>
        </div>
        
        <!-- Submit Buttons -->
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary btn-lg me-3">
                <i class="fas fa-file-pdf"></i> Generate PDF / Tạo chứng chỉ PDF
            </button>
            <button type="button" class="btn btn-success btn-lg me-3" onclick="generateJPG()">
                <i class="fas fa-image"></i> Generate JPG / Tạo chứng chỉ JPG
            </button>
            <button type="button" class="btn btn-secondary btn-lg" onclick="previewPDF()">
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
@endsection

@push('scripts')
<script>
    console.log("New Doctor Certificate page loaded");
    
    // Auto-uppercase input
    document.getElementById('doctor_name').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const doctorName = document.getElementById('doctor_name').value.trim();
        
        if (!doctorName) {
            e.preventDefault();
            alert('Vui lòng nhập tên bác sĩ!');
            document.getElementById('doctor_name').style.borderColor = 'red';
            document.getElementById('doctor_name').focus();
        } else {
            document.getElementById('doctor_name').style.borderColor = '';
        }
    });

    function previewPDF() {
        const form = document.getElementById('newDoctorForm');
        form.action = "{{ route('new-doctor.preview.pdf') }}";
        form.submit();
    }

    function generateJPG() {
        const form = document.getElementById('newDoctorForm');
        form.action = "{{ route('new-doctor.generate.jpg') }}";
        form.submit();
    }
</script>
@endpush
