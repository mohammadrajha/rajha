<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    private const API_URL = 'http://localhost:3000/api/rooms/full-details';

    public function index()
    {
        return view('admin.sync.rooms');
    }

    public function syncRooms(Request $request): JsonResponse
    {
        try {
            $response = Http::timeout(30)->acceptJson()->get(self::API_URL);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => "API request failed with status {$response->status()}.",
                    'synced'  => 0,
                ], 502);
            }

            $payload = $response->json();
            $rows = $this->extractRows($payload);

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API returned no room schedule data.',
                    'synced'  => 0,
                ], 422);
            }

            $synced = 0;

            DB::transaction(function () use ($rows, &$synced) {
                foreach ($rows as $row) {
                    $data = $this->mapRow($row);

                    if (empty($data['room_no']) || empty($data['day']) || empty($data['start_time'])) {
                        continue;
                    }

                    RoomSchedule::updateOrCreate(
                        [
                            'room_no'    => $data['room_no'],
                            'day'        => $data['day'],
                            'start_time' => $data['start_time'],
                        ],
                        [
                            'end_time'        => $data['end_time'],
                            'semester'        => $data['semester'],
                            'dept_no'         => $data['dept_no'],
                            'course_name'     => $data['course_name'],
                            'instructor_name' => $data['instructor_name'],
                        ]
                    );

                    $synced++;
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Sync completed successfully. {$synced} record(s) synced.",
                'synced'  => $synced,
            ]);
        } catch (\Throwable $e) {
            Log::error('Room sync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'synced'  => 0,
            ], 500);
        }
    }

    private function extractRows($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        if (isset($payload['rooms']) && is_array($payload['rooms'])) {
            return $payload['rooms'];
        }

        return $payload;
    }

    private function mapRow(array $row): array
    {
        return [
            'room_no'         => $row['ROOM_NO']         ?? $row['room_no']         ?? null,
            'day'             => $row['DAY']             ?? $row['day']             ?? null,
            'start_time'      => $row['START_TIME']      ?? $row['start_time']      ?? null,
            'end_time'        => $row['END_TIME']        ?? $row['end_time']        ?? null,
            'semester'        => $row['SEMESTER']        ?? $row['semester']        ?? null,
            'dept_no'         => $row['DEPT_NO']         ?? $row['dept_no']         ?? null,
            'course_name'     => $row['COURSE_NAME']     ?? $row['course_name']     ?? null,
            'instructor_name' => $row['INSTRUCTOR_NAME'] ?? $row['instructor_name'] ?? null,
        ];
    }
}
