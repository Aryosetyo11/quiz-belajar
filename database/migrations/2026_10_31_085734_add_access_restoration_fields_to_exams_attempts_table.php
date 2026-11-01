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
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->unsignedSmallInteger('extra_time_minutes')->default(0)->after('question_ids');
            $table->json('access_restorations')->nullable()->after('extra_time_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropColumn(['extra_time_minutes', 'access_restorations']);
        });
    }
};
