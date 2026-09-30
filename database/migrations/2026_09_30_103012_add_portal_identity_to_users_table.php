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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('email')->nullable()->change();
            $table->enum('role', ['guru', 'siswa'])->default('siswa')->index();
            $table->char('nisn', 10)->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['nisn']);
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'nisn']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
