<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Classroom;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ──
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@university.edu',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // ── Departments ──
        $csDept = Department::create([
            'name' => 'Computer Science',
            'name_ar' => 'علوم الحاسوب',
            'code' => 'CS',
        ]);

        $mathDept = Department::create([
            'name' => 'Mathematics',
            'name_ar' => 'الرياضيات',
            'code' => 'MATH',
        ]);

        $engDept = Department::create([
            'name' => 'Engineering',
            'name_ar' => 'الهندسة',
            'code' => 'ENG',
        ]);

        // ── Head of Department Users ──
        $hodCsUser = User::create([
            'name' => 'Dr. Ahmad Khalil',
            'email' => 'ahmad.khalil@university.edu',
            'password' => Hash::make('password'),
            'role' => 'head_of_department',
        ]);
        $csDept->update(['head_user_id' => $hodCsUser->id]);

        $hodMathUser = User::create([
            'name' => 'Dr. Sara Hassan',
            'email' => 'sara.hassan@university.edu',
            'password' => Hash::make('password'),
            'role' => 'head_of_department',
        ]);
        $mathDept->update(['head_user_id' => $hodMathUser->id]);

        // ── Classrooms ──
        $classrooms = [];
        $classroomData = [
            ['name' => 'Room A101', 'name_ar' => 'قاعة A101', 'building' => 'Building A', 'floor' => '1st Floor', 'capacity' => 40],
            ['name' => 'Room A102', 'name_ar' => 'قاعة A102', 'building' => 'Building A', 'floor' => '1st Floor', 'capacity' => 35],
            ['name' => 'Room B201', 'name_ar' => 'قاعة B201', 'building' => 'Building B', 'floor' => '2nd Floor', 'capacity' => 60],
            ['name' => 'Room B202', 'name_ar' => 'قاعة B202', 'building' => 'Building B', 'floor' => '2nd Floor', 'capacity' => 50],
            ['name' => 'Lab C301', 'name_ar' => 'مختبر C301', 'building' => 'Building C', 'floor' => '3rd Floor', 'capacity' => 30],
            ['name' => 'Lab C302', 'name_ar' => 'مختبر C302', 'building' => 'Building C', 'floor' => '3rd Floor', 'capacity' => 25],
            ['name' => 'Auditorium D', 'name_ar' => 'قاعة محاضرات D', 'building' => 'Building D', 'floor' => 'Ground', 'capacity' => 200],
        ];

        foreach ($classroomData as $data) {
            $classrooms[] = Classroom::create($data);
        }

        // ── Instructor Users & Profiles ──
        $instructorData = [
            ['name' => 'Dr. Mohammad Ali', 'name_ar' => 'د. محمد علي', 'email' => 'mohammad.ali@university.edu', 'dept' => $csDept, 'title' => 'Professor'],
            ['name' => 'Dr. Fatima Nasser', 'name_ar' => 'د. فاطمة ناصر', 'email' => 'fatima.nasser@university.edu', 'dept' => $csDept, 'title' => 'Associate Professor'],
            ['name' => 'Dr. Omar Youssef', 'name_ar' => 'د. عمر يوسف', 'email' => 'omar.youssef@university.edu', 'dept' => $mathDept, 'title' => 'Professor'],
            ['name' => 'Dr. Layla Ibrahim', 'name_ar' => 'د. ليلى إبراهيم', 'email' => 'layla.ibrahim@university.edu', 'dept' => $mathDept, 'title' => 'Assistant Professor'],
            ['name' => 'Dr. Khaled Mansour', 'name_ar' => 'د. خالد منصور', 'email' => 'khaled.mansour@university.edu', 'dept' => $engDept, 'title' => 'Professor'],
            ['name' => 'Dr. Nour Saleh', 'name_ar' => 'د. نور صالح', 'email' => 'nour.saleh@university.edu', 'dept' => $engDept, 'title' => 'Lecturer'],
        ];

        $instructors = [];
        foreach ($instructorData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'instructor',
            ]);

            $instructors[] = Instructor::create([
                'user_id' => $user->id,
                'department_id' => $data['dept']->id,
                'name' => $data['name'],
                'name_ar' => $data['name_ar'],
                'email' => $data['email'],
                'title' => $data['title'],
            ]);
        }

        // ── Schedules ──
        $today = Carbon::today()->dayOfWeek;

        $scheduleData = [
            // Today's schedules (for testing)
            ['instructor' => 0, 'classroom' => 0, 'course' => 'Data Structures', 'course_ar' => 'هياكل البيانات', 'code' => 'CS201', 'day' => $today, 'start' => '08:00', 'end' => '09:30'],
            ['instructor' => 0, 'classroom' => 2, 'course' => 'Algorithms', 'course_ar' => 'الخوارزميات', 'code' => 'CS301', 'day' => $today, 'start' => '10:00', 'end' => '11:30'],
            ['instructor' => 1, 'classroom' => 1, 'course' => 'Database Systems', 'course_ar' => 'أنظمة قواعد البيانات', 'code' => 'CS305', 'day' => $today, 'start' => '09:00', 'end' => '10:30'],
            ['instructor' => 2, 'classroom' => 3, 'course' => 'Calculus II', 'course_ar' => 'تفاضل وتكامل 2', 'code' => 'MATH201', 'day' => $today, 'start' => '08:30', 'end' => '10:00'],
            ['instructor' => 3, 'classroom' => 0, 'course' => 'Linear Algebra', 'course_ar' => 'الجبر الخطي', 'code' => 'MATH301', 'day' => $today, 'start' => '11:00', 'end' => '12:30'],
            ['instructor' => 4, 'classroom' => 6, 'course' => 'Circuit Analysis', 'course_ar' => 'تحليل الدوائر', 'code' => 'ENG201', 'day' => $today, 'start' => '12:00', 'end' => '13:30'],
            ['instructor' => 5, 'classroom' => 4, 'course' => 'Digital Logic', 'course_ar' => 'المنطق الرقمي', 'code' => 'ENG301', 'day' => $today, 'start' => '14:00', 'end' => '15:30'],

            // Other days
            ['instructor' => 0, 'classroom' => 4, 'course' => 'Operating Systems', 'course_ar' => 'أنظمة التشغيل', 'code' => 'CS401', 'day' => ($today + 1) % 7, 'start' => '09:00', 'end' => '10:30'],
            ['instructor' => 1, 'classroom' => 5, 'course' => 'Networks', 'course_ar' => 'شبكات الحاسوب', 'code' => 'CS310', 'day' => ($today + 1) % 7, 'start' => '11:00', 'end' => '12:30'],
            ['instructor' => 2, 'classroom' => 3, 'course' => 'Statistics', 'course_ar' => 'الإحصاء', 'code' => 'MATH205', 'day' => ($today + 2) % 7, 'start' => '08:00', 'end' => '09:30'],
        ];

        $schedules = [];
        foreach ($scheduleData as $data) {
            $schedules[] = Schedule::create([
                'instructor_id' => $instructors[$data['instructor']]->id,
                'classroom_id' => $classrooms[$data['classroom']]->id,
                'course_name' => $data['course'],
                'course_name_ar' => $data['course_ar'],
                'course_code' => $data['code'],
                'day_of_week' => $data['day'],
                'start_time' => $data['start'],
                'end_time' => $data['end'],
                'semester' => 'Fall 2024',
                'academic_year' => '2024-2025',
            ]);
        }

        // ── Sample Attendance Logs (past 7 days) ──
        $statuses = ['present', 'present', 'present', 'late', 'missed'];

        for ($day = 6; $day >= 1; $day--) {
            $date = Carbon::today()->subDays($day);
            if ($date->isWeekend()) continue;

            foreach ($schedules as $index => $schedule) {
                if ($schedule->day_of_week !== $date->dayOfWeek) continue;

                $status = $statuses[array_rand($statuses)];
                $delay = $status === 'late' ? rand(12, 30) : ($status === 'present' ? rand(0, 10) : 0);

                AttendanceLog::create([
                    'instructor_id' => $schedule->instructor_id,
                    'schedule_id' => $schedule->id,
                    'classroom_id' => $schedule->classroom_id,
                    'status' => $status,
                    'scanned_at' => $date->copy()->setTimeFromTimeString($schedule->start_time)->addMinutes($delay),
                    'delay_minutes' => $delay,
                ]);
            }
        }
    }
}
