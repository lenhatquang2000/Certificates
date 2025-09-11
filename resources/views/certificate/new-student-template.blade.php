<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>New Student Certificate | PiSystem</title>
    <meta name="viewport" content="width=1192, initial-scale=1">
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
            width: 1192px;
            height: 1482px;
            margin: 0; 
            padding: 0;
            background: transparent;
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }
        body {
            width: 1192px;
            height: 1482px;
            overflow: hidden;
        }
        @media print {
            html, body {
                width: 1192px !important;
                height: 1482px !important;
                max-width: 1192px !important;
                max-height: 1482px !important;
                min-width: 1192px !important;
                min-height: 1482px !important;
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
            width: 1192px !important;
            height: 1482px !important;
            background: url('data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('assets/newDoctorTemplate/' . $processedData['background_image']))) }}') no-repeat;
            background-size: 100% 100%; /* Lấp đầy container mà không cắt xén */
            margin: 0 !important;
            padding: 0 !important;
            position: relative;
            box-sizing: border-box;
        }
        #certificateContent {
            width: 1192px;
            height: 1482px;
            position: relative;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Font definitions */
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
        
        /* Student Name - Sử dụng font UVN Nguyen Du với màu vàng */
        .student-name {
            font-size: 56pt;
            text-transform: uppercase;
            color: #f8f316; /* Màu vàng */
            line-height: 1.2;
            margin: 0;
            /* Sử dụng đúng font UVN Nguyen Du như yêu cầu */
            font-family: 'UVN Nguyen Du', Arial, Helvetica, sans-serif;
            position: absolute;
            top: 924px; /* Điều chỉnh vị trí theo ảnh nền */
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            letter-spacing: 2px;
            text-align: center;
            /* Tăng khoảng cách giữa dấu mũ (dấu thanh) và ký tự */
            /* Sử dụng thuộc tính font-feature-settings để tăng khoảng cách dấu */
        }
    </style>
</head>
<body>
    <div class="certificate-preview-bg">
        <div id="certificateContent">
            <div class="student-name">{{ strtoupper(trim($processedData['student_name'])) }}</div>
        </div>
    </div>
</body>
</html>
