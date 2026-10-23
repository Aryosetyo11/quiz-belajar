<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_materials', function (Blueprint $table): void {
            $table->string('type', 20)->default('file')->after('description');
            $table->text('video_url')->nullable()->after('type');
            $table->string('file_path')->nullable()->change();
            $table->string('original_file_name')->nullable()->change();
            $table->unsignedBigInteger('file_size')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('subject_materials')->where('type', 'video')->exists()) {
            throw new LogicException('Hapus materi video sebelum membatalkan migrasi.');
        }

        Schema::table('subject_materials', function (Blueprint $table): void {
            $table->dropColumn(['type', 'video_url']);
            $table->string('file_path')->nullable(false)->change();
            $table->string('original_file_name')->nullable(false)->change();
            $table->unsignedBigInteger('file_size')->nullable(false)->change();
        });
    }
};
