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
        Schema::table('exam_questions', function (Blueprint $table): void {
            $table->string('type', 20)->default('multiple_choice')->after('exam_id');
            $table->json('options')->nullable()->change();
            $table->char('answer_key', 1)->nullable()->change();
            $table->text('sample_answer')->nullable()->after('answer_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table): void {
            $table->dropColumn(['type', 'sample_answer']);
            $table->json('options')->nullable(false)->change();
            $table->char('answer_key', 1)->nullable(false)->change();
        });
    }
};
