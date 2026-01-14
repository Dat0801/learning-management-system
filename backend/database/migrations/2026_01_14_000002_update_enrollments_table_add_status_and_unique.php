<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active')->after('enrolled_at');
            $table->unique(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'course_id']);
            $table->dropColumn('status');
        });
    }
};

