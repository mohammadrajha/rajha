<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_emails', function (Blueprint $table) {
            $table->id();
            $table->integer('dept_no')->unique();
            $table->string('dept_name')->nullable();
            $table->string('head_email')->nullable();
            $table->string('head_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_emails');
    }
};
