<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra File Excel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 2.5em;
        }
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .error-card {
            background: #f8d7da;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .success-card {
            background: #d4edda;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #666;
            font-size: 1.1em;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        .file-path {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            word-break: break-all;
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .refresh-btn {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Kiểm tra File Excel</h1>
        </div>

        @if($error)
            <div class="error-card">
                <h3>❌ Lỗi</h3>
                <p>{{ $error }}</p>
                <div class="file-path">
                    <strong>Đường dẫn file:</strong> {{ $filePath }}
                </div>
            </div>
        @else
            <div class="success-card">
                <h3>✅ Thành công</h3>
                <p>Đã đọc file Excel thành công!</p>
                <div class="file-path">
                    <strong>Đường dẫn file:</strong> {{ $filePath }}
                </div>
            </div>

            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">{{ $rowCount }}</div>
                    <div class="stat-label">Dòng có dữ liệu</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $totalRows ?? 0 }}</div>
                    <div class="stat-label">Tổng số dòng</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ count($data) }}</div>
                    <div class="stat-label">Mục dữ liệu</div>
                </div>
            </div>

            @if(count($data) > 0)
                <div class="info-card">
                    <h3>📋 Chi tiết dữ liệu</h3>
                    <p>Dưới đây là danh sách các dòng có dữ liệu trong cột đầu tiên:</p>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Dòng Excel</th>
                                <th>Giá trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['row'] }}</td>
                                    <td>{{ $item['value'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="info-card">
                    <h3>📋 Không có dữ liệu</h3>
                    <p>File Excel không chứa dữ liệu nào trong cột đầu tiên.</p>
                </div>
            @endif
        @endif

        <div class="refresh-btn">
            <a href="{{ route('excel.check') }}" class="btn">🔄 Làm mới</a>
            <a href="{{ route('excel.check.api') }}" class="btn btn-secondary">📡 Xem JSON</a>
            <a href="{{ route('welcome') }}" class="btn btn-secondary">🏠 Trang chủ</a>
        </div>
    </div>
</body>
</html>
