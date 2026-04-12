<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepartmentEmail;
use Illuminate\Http\Request;

class DepartmentEmailController extends Controller
{
    public function index()
    {
        $departments = DepartmentEmail::orderBy('dept_no')->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function edit(DepartmentEmail $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, DepartmentEmail $department)
    {
        $validated = $request->validate([
            'dept_name' => 'nullable|string|max:255',
            'head_email' => 'nullable|email|max:255',
            'head_name' => 'nullable|string|max:255',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', __('messages.department_updated'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dept_no' => 'required|integer|unique:department_emails,dept_no',
            'dept_name' => 'nullable|string|max:255',
            'head_email' => 'nullable|email|max:255',
            'head_name' => 'nullable|string|max:255',
        ]);

        DepartmentEmail::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', __('messages.department_created'));
    }
}
