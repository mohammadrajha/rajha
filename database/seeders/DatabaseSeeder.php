<?php

namespace Database\Seeders;

use App\Models\DepartmentEmail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ──
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@university.edu',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // ── Sample HOD user ──
        User::create([
            'name' => 'Dr. Ahmad Khalil',
            'email' => 'ahmad.khalil@university.edu',
            'password' => Hash::make('password'),
            'role' => 'head_of_department',
        ]);

        // ── Sample Instructor user ──
        // instructor_name must match exactly the value returned by the room API.
        User::create([
            'name' => 'Dr. Mohammad Ali',
            'email' => 'mohammad.ali@university.edu',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'instructor_name' => null,
        ]);

        // ── Sample department mapping ──
        // Real dept_no entries are auto-created when rooms are scanned.
        DepartmentEmail::create([
            'dept_no' => 1,
            'dept_name' => 'Sample Department',
            'head_name' => 'Dr. Ahmad Khalil',
            'head_email' => 'ahmad.khalil@university.edu',
        ]);
    }
}
