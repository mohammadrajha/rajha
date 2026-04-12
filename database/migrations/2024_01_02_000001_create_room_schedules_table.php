<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('room_no')->index();
            $table->string('day');            // e.g. "الأحد"
            $table->string('start_time');     // e.g. "10:00"
            $table->string('end_time');       // e.g. "11:00"
            $table->integer('semester');       // e.g. 20252
            $table->integer('dept_no');
            $table->string('course_name');
            $table->string('instructor_name');
            $table->timestamps();

            $table->index(['room_no', 'semester']);
            $table->index(['instructor_name', 'semester']);
            $table->index('dept_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_schedules');
    }
};
