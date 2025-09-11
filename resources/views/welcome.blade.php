@extends('layouts.main')

@section('title', 'Welcome | PiSystem')

@section('content')
    <div class="welcome">
        <h2>Welcome to PiSystem</h2>
        <p>
            PiSystem is your centralized platform for managing modules, reports, and system settings.  
            Use the navigation above to explore available features.
        </p>
        
        <div class="quick-links mb-4">
            <a href="{{ route('certificate.create') }}">Create Certificate</a> |
            <a href="{{ route('new-doctor.create') }}">Create New Doctor</a>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="fa fa-certificate text-primary"></i> Certificate</h5>
                        <p class="card-text">Tạo giấy khen cho sinh viên xuất sắc/giỏi/tiên tiến</p>
                        <a href="{{ route('certificate.create') }}" class="btn btn-primary">
                            <i class="fa fa-certificate me-1"></i>
                            Create Certificate
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title"><i class="fa fa-user-md text-success"></i> New Doctor</h5>
                        <p class="card-text">Tạo chứng chỉ tân bác sĩ</p>
                        <a href="{{ route('new-doctor.create') }}" class="btn btn-success">
                            <i class="fa fa-user-md me-1"></i>
                            Create New Doctor Certificate
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    console.log("Welcome page loaded in PiSystem");
</script>
@endpush
