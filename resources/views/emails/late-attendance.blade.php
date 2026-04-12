<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .body { background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .footer { padding: 15px 20px; text-align: center; font-size: 12px; color: #9ca3af; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .status-late { background: #fef3c7; color: #92400e; }
        .status-missed { background: #fee2e2; color: #991b1b; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .info-table td:first-child { color: #6b7280; width: 40%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Attendance Alert / تنبيه حضور</h2>
        </div>
        <div class="body">
            <p>
                <span class="status-badge {{ $log->status === 'missed' ? 'status-missed' : 'status-late' }}">
                    {{ strtoupper($log->status) }}
                </span>
            </p>

            <table class="info-table">
                <tr>
                    <td>Instructor / المدرس</td>
                    <td><strong>{{ $log->instructor_name }}</strong></td>
                </tr>
                <tr>
                    <td>Course / المادة</td>
                    <td><strong>{{ $log->course_name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td>Room / القاعة</td>
                    <td>{{ $log->room_no }}</td>
                </tr>
                <tr>
                    <td>Status / الحالة</td>
                    <td><strong>{{ ucfirst($log->status) }}</strong></td>
                </tr>
                @if($log->delay_minutes > 0)
                <tr>
                    <td>Delay / التأخير</td>
                    <td>{{ $log->delay_minutes }} minutes</td>
                </tr>
                @endif
                <tr>
                    <td>Date / التاريخ</td>
                    <td>{{ $log->scanned_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            QR Attendance System - {{ config('app.name') }}
        </div>
    </div>
</body>
</html>
