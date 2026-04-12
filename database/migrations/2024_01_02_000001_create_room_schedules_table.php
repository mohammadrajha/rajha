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
            // Internal room identifier from the upstream API (ROOM_NO in Oracle).
            // Not shown in the UI; kept so we can call the single-room refresh API.
            $table->unsignedInteger('room_id')->nullable()->index();
            // Public classroom code shown to users (ROOM_CODE in Oracle), e.g. "10018".
            $table->string('room_no')->index();
            // Human-readable room description (ROOM_DESC in Oracle), e.g. "ق 18 طابق أرضي".
            $table->string('room_desc')->nullable();
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
