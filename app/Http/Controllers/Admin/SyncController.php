<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use App\Services\ExternalApiService;

class SyncController extends Controller
{
    public function index()
    {
        $logs = SyncLog::latest()->paginate(20);
        return view('admin.sync.index', compact('logs'));
    }

    public function syncNow(ExternalApiService $apiService)
    {
        $results = $apiService->syncAll();

        return redirect()->route('admin.sync.index')
            ->with('success', __('messages.sync_completed'));
    }
}
