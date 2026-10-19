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
            background-color: #0d1527;
            color: #dae2fd;
        }
        .cert-container {
            border: 3px solid #10b981;
            border-radius: 8px;
            padding: 30px 40px;
            box-sizing: border-box;
            background: linear-gradient(135deg, #0b1326 0%, #131b2e 100%);
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
            color: #4edea3;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .subbrand {
            font-size: 11px;
            color: #bbcabf;
            letter-spacing: 1px;
            margin-top: 4px;
        }
        .title {
            font-size: 30px;
            font-weight: 800;
            color: #ffffff;
            margin-top: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .statement-intro {
            font-size: 13px;
            color: #bbcabf;
            text-align: center;
            margin-top: 15px;
        }
        .recipient {
            font-size: 28px;
            font-weight: bold;
            color: #4edea3;
            text-align: center;
            margin: 10px 0;
            border-bottom: 2px solid rgba(78, 222, 163, 0.4);
            display: inline-block;
            padding: 0 40px 6px 40px;
        }
        .recipient-box {
            text-align: center;
        }
        .statement {
            font-size: 13px;
            line-height: 1.6;
            color: #dae2fd;
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
            color: #ffb95f;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }
        .module-item {
            display: inline-block;
            background-color: #171f33;
            border: 1px solid #3c4a42;
            color: #dae2fd;
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
            color: #ffffff;
            border-bottom: 1px solid #86948a;
            display: inline-block;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }
        .signatory-title {
            color: #bbcabf;
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
            color: #4edea3;
            letter-spacing: 1px;
        }
        .cert-date {
            color: #bbcabf;
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
                    <div style="font-size: 9px; color: #86948a; margin-top: 4px;">Verifikasi keaslian di: {{ url('/certificates/verify/' . $certificateCode) }}</div>
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
