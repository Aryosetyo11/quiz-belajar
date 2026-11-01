<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_materials', function (Blueprint $table): void {
            $table->text('external_url')->nullable()->after('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('subject_materials', function (Blueprint $table): void {
            $table->dropColumn('external_url');
        });
    }
};
