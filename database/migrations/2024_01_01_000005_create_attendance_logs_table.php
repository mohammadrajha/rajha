<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('instructor_name');
            $table->integer('room_no');
            $table->foreignId('room_schedule_id')->nullable()->constrained('room_schedules')->nullOnDelete();
            $table->integer('dept_no')->nullable();
            $table->string('course_name')->nullable();
            $table->enum('status', ['present', 'late', 'wrong_classroom', 'no_lecture', 'missed']);
            $table->timestamp('scanned_at');
            $table->integer('delay_minutes')->default(0);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['instructor_name', 'scanned_at']);
            $table->index('room_no');
            $table->index('dept_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
