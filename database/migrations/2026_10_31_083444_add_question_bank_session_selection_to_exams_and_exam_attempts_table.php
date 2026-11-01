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
        Schema::table('exams', function (Blueprint $table): void {
            $table->string('type', 32)->default('session')->after('subject_id');
            $table->foreignId('bank_package_id')->nullable()->after('type')->constrained('exams')->restrictOnDelete();
            $table->unsignedSmallInteger('question_count')->nullable()->after('duration_minutes');
            $table->unsignedSmallInteger('essay_question_count')->default(0)->after('question_count');
            $table->index(['subject_id', 'type', 'is_published']);
        });

        DB::table('exams')
            ->whereIn('description', [
                'Paket soal disusun secara manual ke Bank Soal.',
                'Paket soal diimpor ke Bank Soal.',
            ])
            ->update(['type' => 'question_bank']);

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->json('question_ids')->nullable()->after('answers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropColumn('question_ids');
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropIndex(['subject_id', 'type', 'is_published']);
            $table->dropConstrainedForeignId('bank_package_id');
            $table->dropColumn(['type', 'question_count', 'essay_question_count']);
        });
    }
};
