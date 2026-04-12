<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('rooms_qr.room') }} {{ $roomNo }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #fff;
        }
        .card {
            text-align: center;
            padding: 40px;
            border: 2px solid #111;
            border-radius: 16px;
        }
        .room {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 16px;
        }
        .hint {
            font-size: 14px;
            color: #666;
            margin-top: 16px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div>
        <div class="card">
            <div class="room">{{ __('rooms_qr.room') }} {{ $roomNo }}</div>
            <div>{!! $svg !!}</div>
            <div class="hint">{{ __('rooms_qr.scan_hint') }}</div>
        </div>
        <div class="no-print" style="text-align:center; margin-top:20px;">
            <button onclick="window.print()" style="padding:10px 20px; font-size:14px; cursor:pointer;">
                {{ __('rooms_qr.print') }}
            </button>
        </div>
    </div>
</body>
</html>
