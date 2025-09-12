<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Certificate Preview | PiSystem</title>
    <meta name="viewport" content="width=842, initial-scale=1">
    <style>
        /* CSS RESET */
        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed, 
        figure, figcaption, footer, header, hgroup, 
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
            box-sizing: border-box;
        }
        /* HTML5 display-role reset for older browsers */
        article, aside, details, figcaption, figure, 
        footer, header, hgroup, menu, nav, section {
            display: block;
        }
        body {
            line-height: 1;
        }
        ol, ul {
            list-style: none;
        }
        blockquote, q {
            quotes: none;
        }
        blockquote:before, blockquote:after,
        q:before, q:after {
            content: '';
            content: none;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }
        /* END CSS RESET */

        html, body {
            width: 842px;
            height: 595px;
            margin: 0; 
            padding: 0;
            background: transparent;
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }
        body {
            width: 842px;
            height: 595px;
            overflow: hidden;
        }
        @media print {
            html, body {
                width: 842px !important;
                height: 595px !important;
                max-width: 842px !important;
                max-height: 595px !important;
                min-width: 842px !important;
                min-height: 595px !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                box-sizing: border-box !important;
            }
            body {
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
            }
            .certificate-preview-bg, #certificateContent {
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
            }
        }
        .certificate-preview-bg {
            width: 842px !important;
            height: 595px !important;
            background: url('data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('assets/template/certificate_template.jpg'))) }}') no-repeat;
            background-size: 100% 100%; /* Lấp đầy container mà không cắt xén */
            margin: 0 !important;
            padding: 0 !important;
            position: relative;
        }
        #certificateContent {
            width: 842px;
            height: 595px;
            position: absolute;
            top: 167px;
            left: 0;
            box-sizing: border-box;
        }
        @font-face {
            font-family: 'UTM HelvetIns';
            src: url('data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/UTM HelvetIns.ttf'))) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'UVN Nguyen Du';
            src: url('data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/unicode.display.UVNNguyenDu.TTF'))) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Times New Roman Bold Custom';
            src: url('data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/timesbd_0.ttf'))) }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'Times New Roman Custom';
            src: url('data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/times_0.ttf'))) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'SVN-Agency FB';
            src: url('data:font/truetype;base64,{{ base64_encode(file_get_contents(public_path('fonts/SVN-Agency FB.ttf'))) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        .recipient-name {
            font-size: 30pt;
            text-transform: uppercase;
            color: #d4af37;
            line-height: 0.8;
            margin: {{ $data['position_adjustments']['recipient_name_top'] ?? 0 }}px 0 4px 0;
            font-family: 'UTM HelvetIns', Times, 'Times New Roman', serif;
            position: absolute;
            top: 99px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            letter-spacing: -1px;
        }
        .program {
            font-size: 20pt;
            color: #d4af37;
            margin: {{ $data['position_adjustments']['program_top'] ?? 0 }}px 0 0px 0;
            text-transform: uppercase;
            font-family: 'UVN Nguyen Du', Arial, Helvetica, sans-serif;
            position: absolute;
            top: 154px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
        }
        .program-english {
            font-size: 20pt;
            color: #8a2323;
            margin: {{ $data['position_adjustments']['program_english_top'] ?? 0 }}px 0 4px 0;
            font-family: 'SVN-Agency FB', Arial, Helvetica, sans-serif;
            font-weight: 400;
            letter-spacing: 0.1px;
            position: absolute;
            top: 187px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
        }
        .award-title {
            font-size: 20pt;
            margin: {{ $data['position_adjustments']['award_title_top'] ?? 0 }}px 0 0px 0;
            font-family: 'UVN Nguyen Du', Arial, Helvetica, sans-serif;
            color: #d4af37;
            position: absolute;
            top: 219px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
        }
        .award-title-english {
            font-size: 18pt;
            font-style: normal;
            color: #8a2323;
            margin: {{ $data['position_adjustments']['award_title_english_top'] ?? 0 }}px 0 1px 0;
            font-family: 'SVN-Agency FB', Arial, Helvetica, sans-serif;
            font-weight: 400;
            letter-spacing: 0.1px;
            position: absolute;
            top: 248px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
        }
        .right-block {
            position: absolute;
            top: 50%; /* Căn giữa theo chiều dọc */
            right: 20px; /* Căn phải cách lề phải 20px */
            transform: translateY(-50%); /* Điều chỉnh để căn chính giữa theo chiều dọc */
            width: 220px; /* Chiều rộng cố định */
            display: flex;
            flex-direction: column;
            gap: 2px; /* Tăng khoảng cách giữa các item bên trong */
            box-sizing: border-box;
            padding: 0;
            margin: 0;
            bottom: 268px;
            right: 70px;
            text-align: right; /* Đảm bảo nội dung bên trong căn phải */
            white-space: nowrap; /* Ngăn chữ xuống dòng */
        }
        .vietnamese-date {
            font-size: 9pt;
            margin: {{ $data['position_adjustments']['vietnamese_date_top'] ?? 0 }}px 0 1px 0;
            font-family: 'Times New Roman', Times, serif;
            color: #8a2323;
            font-weight: 400;
            font-style: italic;
            white-space: nowrap; /* Ngăn chữ xuống dòng */
            text-align: center;
            width: 100%;
        }
        .english-date {
            font-size: 9pt;
            color: #8a2323;
            margin: {{ $data['position_adjustments']['english_date_top'] ?? 0 }}px 0 2px 0;
            font-family: 'Times New Roman', Times, serif;
            font-style: italic;
            white-space: nowrap; /* Ngăn chữ xuống dòng */
            text-align: center;
            width: 100%;
        }
        .rector-label {
            font-size: 9pt;
            font-weight: 600;
            margin: 0px 0 0 0;
            font-family: 'Times New Roman', Times, serif;
            color: #8a2323;
            white-space: nowrap; /* Ngăn chữ xuống dòng */
            text-align: center;
            width: 100%;
        }
        .rector-name {
            font-size: 9pt;
            font-weight: 600;
            margin: 8px 0 0 0;
            font-family: 'Times New Roman', Times, serif;
            color: #8a2323;
            white-space: nowrap; /* Ngăn chữ xuống dòng */
            text-align: center;
            top: -36px;
            width: 100%;
        }
        .decision-number {
            font-size: 8pt;
            margin: {{ $data['position_adjustments']['decision_number_top'] ?? 0 }}px 0 0 0;
            font-family: 'Times New Roman', Times, serif;
            color: #8a2323;
            position: absolute;
            top: 362px;
            left: 106px;
            text-align: left;
        }
        .decision-number-label {
            font-size: 8pt;
            color: #8a2323;
            margin: 0 0 0 0;
            font-family: 'Times New Roman', Times, serif;
            position: absolute;
            top: 376px;
            left: 106px;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="certificate-preview-bg">
        <div id="certificateContent">
            <div class="recipient-name">{{ strtoupper(trim($data['recipient_name'])) }}</div>
            <div class="program">LỚP: {{ strtoupper(trim($data['program'])) }}</div>
            <div class="program-english">(Class: {{ trim($data['program_english']) }})</div>
            <div class="award-title">{{ $data['award_title'] }}</div>
            <div class="award-title-english">({{ $data['award_title_english'] }})</div>
            <div class="right-block">
                <div class="vietnamese-date">{{ $data['vietnamese_date'] }}</div>
                <div class="english-date">{{ $data['english_date'] }}</div>
                <div class="rector-label" style="position: relative; z-index: 1;">HIỆU TRƯỞNG/RECTOR</div>
                <div style="position: relative; margin-top: -10px; z-index: 3;">
                    <div style="height:120px; display:block; margin-left:auto; margin-right:auto; position: relative; z-index: 4; margin-top: -34px; left: -40px"></div>
                </div>
                <div class="rector-name" style="position: relative; z-index: 2;">{{ trim($data['rector_name']) }}</div>
            </div>
            <div class="decision-number">Quyết định số: {{ $data['decision_number'] }}</div>
            <div class="decision-number-label">(Decision No: {{ $data['decision_prefix_english'] }})</div>
        </div>
    </div>
</body>
</html>