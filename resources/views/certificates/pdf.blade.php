<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Penyelesaian - LinuxLab</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 24px;
            background-color: #ffffff;
            color: #4b4b4b;
        }
        .cert-container {
            border: 3px solid #58cc02;
            border-radius: 8px;
            padding: 30px 40px;
            box-sizing: border-box;
            background: #ffffff;
            height: 93%;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .brand {
            font-size: 26px;
            font-weight: bold;
            color: #2f6e00;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .subbrand {
            font-size: 11px;
            color: #6b6b6b;
            letter-spacing: 1px;
            margin-top: 4px;
        }
        .title {
            font-size: 30px;
            font-weight: 800;
            color: #100f3e;
            margin-top: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .statement-intro {
            font-size: 13px;
            color: #6b6b6b;
            text-align: center;
            margin-top: 15px;
        }
        .recipient {
            font-size: 28px;
            font-weight: bold;
            color: #2f6e00;
            text-align: center;
            margin: 10px 0;
            border-bottom: 2px solid rgba(88, 204, 2, 0.5);
            display: inline-block;
            padding: 0 40px 6px 40px;
        }
        .recipient-box {
            text-align: center;
        }
        .statement {
            font-size: 13px;
            line-height: 1.6;
            color: #4b4b4b;
            text-align: center;
            max-width: 750px;
            margin: 10px auto;
        }
        .modules-box {
            margin: 12px auto;
            text-align: center;
        }
        .modules-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #a35c00;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }
        .module-item {
            display: inline-block;
            background-color: #f7f7f7;
            border: 1px solid #e5e5e5;
            color: #4b4b4b;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            margin: 2px 4px;
        }
        .footer-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .footer-table td {
            vertical-align: bottom;
            font-size: 11px;
        }
        .signatory-box {
            text-align: right;
            padding-right: 20px;
        }
        .signatory-name {
            font-size: 13px;
            font-weight: bold;
            color: #100f3e;
            border-bottom: 1px solid #767676;
            display: inline-block;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }
        .signatory-title {
            color: #6b6b6b;
            font-size: 11px;
        }
        .code-box {
            text-align: left;
            padding-left: 20px;
        }
        .cert-code {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            font-weight: bold;
            color: #2f6e00;
            letter-spacing: 1px;
        }
        .cert-date {
            color: #6b6b6b;
            font-size: 11px;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="cert-container">
        <div class="header">
            <div class="brand">LinuxLab</div>
            <div class="subbrand">Platform Pembelajaran Linux Interaktif</div>
            <div class="title">Sertifikat Penyelesaian</div>
        </div>

        <div class="statement-intro">Diberikan dengan bangga kepada:</div>
        <div class="recipient-box">
            <div class="recipient">{{ $recipientName }}</div>
        </div>

        <div class="statement">
            Atas dedikasi dan keberhasilannya dalam menyelesaikan seluruh kurikulum modul pembelajaran dan praktik terminal Linux interaktif pada platform <strong>LinuxLab</strong>.
        </div>

        <div class="modules-box">
            <div class="modules-title">Modul yang Diselesaikan</div>
            @foreach($modules as $module)
                <span class="module-item">{{ $module }}</span>
            @endforeach
        </div>

        <table class="footer-table">
            <tr>
                <td class="code-box" style="width: 50%;">
                    <div class="cert-date">Diterbitkan pada: <strong>{{ $issuedAt }}</strong></div>
                    <div class="cert-date">Kode Verifikasi: <span class="cert-code">{{ $certificateCode }}</span></div>
                    <div style="font-size: 9px; color: #9e9e9e; margin-top: 4px;">Verifikasi keaslian di: {{ url('/certificates/verify/' . $certificateCode) }}</div>
                </td>
                <td class="signatory-box" style="width: 50%;">
                    @if($signatoryName)
                        <div class="signatory-name">{{ $signatoryName }}</div>
                        <div class="signatory-title">{{ $signatoryTitle }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
