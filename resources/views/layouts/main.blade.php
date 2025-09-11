<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PiSystem Portal')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        main.container {
            min-height: 80vh;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-primary text-white p-3 mb-4">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between">
            <h1 class="mb-2 mb-md-0">
                <a href="/" class="text-white text-decoration-none">
                    <i class="bi bi-cpu me-2"></i>PiSystem
                </a>
            </h1>
            <nav>
                <a href="{{ route('welcome') }}" class="text-white me-3"><i class="fa fa-tachometer-alt me-1"></i>Dashboard</a>
                <a href="{{ route('certificate.create') }}" class="text-white me-3"><i class="fa fa-certificate me-1"></i>Certificate</a>
                <a href="{{ route('new-doctor.create') }}" class="text-white me-3"><i class="fa fa-user-md me-1"></i>New Doctor</a>
            </nav>
        </div>
    </header>

    <!-- Main content -->
    <main class="container my-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-auto">
        <p class="mb-0">© 2025 PiSystem – Centralized Information Platform</p>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Base Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    {{-- Scripts riêng của từng trang --}}
    @stack('scripts')
</body>
</html>
