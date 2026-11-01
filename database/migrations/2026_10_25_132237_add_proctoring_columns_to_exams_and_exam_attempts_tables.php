<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->boolean('enable_proctoring')->default(true)->after('is_published');
            $table->unsignedTinyInteger('max_violations')->default(3)->after('enable_proctoring');
        });

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->unsignedSmallInteger('violation_count')->default(0)->after('score');
            $table->json('violation_logs')->nullable()->after('violation_count');
            $table->boolean('is_terminated_by_system')->default(false)->after('violation_logs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropColumn(['violation_count', 'violation_logs', 'is_terminated_by_system']);
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropColumn(['enable_proctoring', 'max_violations']);
        });
    }
};
