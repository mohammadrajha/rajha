<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    public function create()
    {
        return view('admin.instructor-emails.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'instructor_name' => 'nullable|string|max:255',
            'password'        => 'nullable|string|min:6|max:255',
        ]);

        User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'instructor_name' => $validated['instructor_name'] ?? null,
            'password'        => Hash::make($validated['password'] ?? Str::random(12)),
            'role'            => 'instructor',
            'locale'          => app()->getLocale(),
        ]);

        return redirect()->route('admin.instructor-emails.index')
            ->with('success', __('messages.instructor_email_created'));
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

    public function destroy(User $user)
    {
        abort_unless($user->role === 'instructor', 404);

        $user->delete();

        return redirect()->route('admin.instructor-emails.index')
            ->with('success', __('messages.instructor_email_deleted'));
    }
}
