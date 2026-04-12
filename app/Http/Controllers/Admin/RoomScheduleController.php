<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomSchedule;
use Illuminate\Http\Request;

class RoomScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomSchedule::query()->orderBy('room_no')->orderBy('day')->orderBy('start_time');

        if ($request->filled('room_no')) {
            $query->where('room_no', $request->room_no);
        }

        if ($request->filled('instructor_name')) {
            $query->where('instructor_name', 'like', "%{$request->instructor_name}%");
        }

        $schedules = $query->paginate(25)->withQueryString();

        return view('admin.room-schedules.index', compact('schedules'));
    }

    public function edit(RoomSchedule $roomSchedule)
    {
        return view('admin.room-schedules.edit', ['schedule' => $roomSchedule]);
    }

    public function update(Request $request, RoomSchedule $roomSchedule)
    {
        $validated = $request->validate([
            'room_no'         => 'required|integer',
            'day'             => 'required|string|max:20',
            'start_time'      => 'required|string|max:10',
            'end_time'        => 'required|string|max:10',
            'semester'        => 'required|integer',
            'dept_no'         => 'nullable|integer',
            'course_name'     => 'nullable|string|max:255',
            'instructor_name' => 'nullable|string|max:255',
        ]);

        $roomSchedule->update($validated);

        return redirect()->route('admin.room-schedules.index')
            ->with('success', __('messages.room_schedule_updated'));
    }

    public function destroy(RoomSchedule $roomSchedule)
    {
        $roomSchedule->delete();

        return redirect()->route('admin.room-schedules.index')
            ->with('success', __('messages.room_schedule_deleted'));
    }
}
