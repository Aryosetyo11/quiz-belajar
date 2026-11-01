<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->timestamp('started_at')->useCurrent()->change();
        });

        DB::table('exam_attempts')
            ->whereNotNull('submitted_at')
            ->whereNotNull('duration_seconds')
            ->where('duration_seconds', '>', 0)
            ->update([
                'started_at' => DB::raw('DATE_SUB(submitted_at, INTERVAL duration_seconds SECOND)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->timestamp('started_at')->useCurrent()->useCurrentOnUpdate()->change();
        });
    }
};
