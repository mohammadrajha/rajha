<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstructorEmailController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'instructor')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('instructor_name', 'like', "%{$search}%");
            });
        }

        $instructors = $query->paginate(25)->withQueryString();

        return view('admin.instructor-emails.index', compact('instructors'));
    }

    public function edit(User $user)
    {
        abort_unless($user->role === 'instructor', 404);

        return view('admin.instructor-emails.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->role === 'instructor', 404);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'instructor_name' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return redirect()->route('admin.instructor-emails.index')
            ->with('success', __('messages.instructor_email_updated'));
    }
}
