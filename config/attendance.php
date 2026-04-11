<?php

return [
    // Grace period in minutes before marking as late
    'grace_period_minutes' => env('ATTENDANCE_GRACE_PERIOD', 11),

    // Enable GPS validation on scan
    'gps_validation_enabled' => env('ATTENDANCE_GPS_ENABLED', false),

    // Maximum distance (meters) between scan location and classroom
    'gps_max_distance' => env('ATTENDANCE_GPS_MAX_DISTANCE', 100),

    // Prevent duplicate scans for the same schedule on the same day
    'prevent_duplicate_scans' => true,

    // External API base URL for syncing data
    'api_base_url' => env('EXTERNAL_API_BASE_URL', ''),
    'api_key' => env('EXTERNAL_API_KEY', ''),
    'api_timeout' => env('EXTERNAL_API_TIMEOUT', 30),

    // Room schedule API base URL (e.g. https://your-server.com)
    // The service calls GET {base_url}/api/rooms/{roomNo}/full-details
    'room_api_base_url' => env('ROOM_API_BASE_URL', ''),

    // QR code settings
    'qr_size' => env('QR_CODE_SIZE', 300),
    'qr_format' => 'svg',

    // How many minutes before lecture end to allow scanning
    'scan_window_before_end' => 15,
];
