<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function __construct(
        protected QrCodeService $qrService,
    ) {}

    public function index()
    {
        $classrooms = Classroom::withCount('schedules')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        return view('admin.classrooms.form', ['classroom' => new Classroom()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'gps_radius_meters' => 'nullable|integer|min:10|max:1000',
        ]);

        $classroom = Classroom::create($validated);

        return redirect()->route('admin.classrooms.show', $classroom)
            ->with('success', __('messages.classroom_created'));
    }

    public function show(Classroom $classroom)
    {
        $classroom->load('schedules.instructor');
        $qrSvg = $this->qrService->generateSvg($classroom);

        return view('admin.classrooms.show', compact('classroom', 'qrSvg'));
    }

    public function edit(Classroom $classroom)
    {
        return view('admin.classrooms.form', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'gps_radius_meters' => 'nullable|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        $classroom->update($validated);

        return redirect()->route('admin.classrooms.show', $classroom)
            ->with('success', __('messages.classroom_updated'));
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()->route('admin.classrooms.index')
            ->with('success', __('messages.classroom_deleted'));
    }

    public function printQr(Classroom $classroom)
    {
        $qrSvg = $this->qrService->generatePrintable($classroom);
        return view('admin.classrooms.print-qr', compact('classroom', 'qrSvg'));
    }

    public function regenerateToken(Classroom $classroom)
    {
        $classroom->regenerateToken();

        return redirect()->route('admin.classrooms.show', $classroom)
            ->with('success', __('messages.qr_regenerated'));
    }
}
