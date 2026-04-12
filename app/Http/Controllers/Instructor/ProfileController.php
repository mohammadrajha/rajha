<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('instructor.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'instructor_name' => 'required|string|max:255',
        ]);

        $request->user()->update($validated);

        return redirect()->route('instructor.profile')
            ->with('success', __('messages.profile_updated'));
    }
}
