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
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['present', 'late', 'wrong_classroom', 'no_lecture', 'missed']);
            $table->timestamp('scanned_at');
            $table->integer('delay_minutes')->default(0);
            $table->decimal('scan_latitude', 10, 7)->nullable();
            $table->decimal('scan_longitude', 10, 7)->nullable();
            $table->boolean('gps_valid')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['instructor_id', 'scanned_at']);
            $table->index(['schedule_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
