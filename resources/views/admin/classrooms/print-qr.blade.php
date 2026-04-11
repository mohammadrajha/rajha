<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR - {{ $classroom->name }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: white;
        }
        .qr-container {
            text-align: center;
            padding: 40px;
            border: 3px solid #333;
            border-radius: 16px;
        }
        .classroom-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .classroom-info {
            font-size: 16px;
            color: #666;
            margin-bottom: 24px;
        }
        .qr-code {
            margin: 0 auto;
            display: inline-block;
        }
        .qr-code svg {
            width: 400px;
            height: 400px;
        }
        .scan-text {
            margin-top: 24px;
            font-size: 18px;
            color: #333;
        }
        .print-btn {
            margin-top: 30px;
            padding: 12px 32px;
            font-size: 16px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <div class="classroom-name">{{ $classroom->name }}</div>
        @if($classroom->building || $classroom->floor)
            <div class="classroom-info">{{ $classroom->building }} {{ $classroom->floor ? '- ' . $classroom->floor : '' }}</div>
        @endif
        <div class="qr-code">{!! $qrSvg !!}</div>
        <div class="scan-text">Scan to mark attendance / امسح للحضور</div>
    </div>

    <button onclick="window.print()" class="print-btn no-print">Print / طباعة</button>
</body>
</html>
