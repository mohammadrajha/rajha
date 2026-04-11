<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('attendance.api_base_url', '');
        $this->apiKey = config('attendance.api_key', '');
        $this->timeout = config('attendance.api_timeout', 30);
    }

    public function syncInstructors(): SyncLog
    {
        $syncLog = SyncLog::create([
            'type' => 'instructors',
            'status' => 'success',
            'started_at' => now(),
        ]);

        if (empty($this->baseUrl)) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => 'API base URL not configured',
                'completed_at' => now(),
            ]);
            return $syncLog;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Authorization' => "Bearer {$this->apiKey}"])
                ->get("{$this->baseUrl}/instructors");

            if (!$response->successful()) {
                throw new \Exception("API returned status {$response->status()}");
            }

            $data = $response->json('data', []);
            $synced = 0;
            $failed = 0;

            foreach ($data as $item) {
                try {
                    Instructor::updateOrCreate(
                        ['external_id' => $item['id']],
                        [
                            'name' => $item['name'] ?? '',
                            'name_ar' => $item['name_ar'] ?? null,
                            'email' => $item['email'] ?? '',
                            'phone' => $item['phone'] ?? null,
                            'title' => $item['title'] ?? null,
                            'department_id' => $item['department_id'] ?? null,
                        ]
                    );
                    $synced++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::warning("Failed to sync instructor: {$e->getMessage()}", $item);
                }
            }

            $syncLog->update([
                'status' => $failed > 0 ? 'partial' : 'success',
                'records_synced' => $synced,
                'records_failed' => $failed,
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            Log::error("Instructor sync failed: {$e->getMessage()}");
        }

        return $syncLog;
    }

    public function syncSchedules(): SyncLog
    {
        $syncLog = SyncLog::create([
            'type' => 'schedules',
            'status' => 'success',
            'started_at' => now(),
        ]);

        if (empty($this->baseUrl)) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => 'API base URL not configured',
                'completed_at' => now(),
            ]);
            return $syncLog;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Authorization' => "Bearer {$this->apiKey}"])
                ->get("{$this->baseUrl}/schedules");

            if (!$response->successful()) {
                throw new \Exception("API returned status {$response->status()}");
            }

            $data = $response->json('data', []);
            $synced = 0;
            $failed = 0;

            foreach ($data as $item) {
                try {
                    $instructor = Instructor::where('external_id', $item['instructor_id'])->first();
                    if (!$instructor) {
                        $failed++;
                        continue;
                    }

                    Schedule::updateOrCreate(
                        ['external_id' => $item['id']],
                        [
                            'instructor_id' => $instructor->id,
                            'classroom_id' => $item['classroom_id'],
                            'course_name' => $item['course_name'] ?? '',
                            'course_name_ar' => $item['course_name_ar'] ?? null,
                            'course_code' => $item['course_code'] ?? null,
                            'day_of_week' => $item['day_of_week'],
                            'start_time' => $item['start_time'],
                            'end_time' => $item['end_time'],
                            'semester' => $item['semester'] ?? null,
                            'academic_year' => $item['academic_year'] ?? null,
                        ]
                    );
                    $synced++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::warning("Failed to sync schedule: {$e->getMessage()}", $item);
                }
            }

            $syncLog->update([
                'status' => $failed > 0 ? 'partial' : 'success',
                'records_synced' => $synced,
                'records_failed' => $failed,
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            Log::error("Schedule sync failed: {$e->getMessage()}");
        }

        return $syncLog;
    }

    public function syncAll(): array
    {
        return [
            'instructors' => $this->syncInstructors(),
            'schedules' => $this->syncSchedules(),
        ];
    }
}
