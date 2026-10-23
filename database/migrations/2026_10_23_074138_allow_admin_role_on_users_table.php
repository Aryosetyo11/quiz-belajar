<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 20)->default('siswa')->change();
        });
    }

    public function down(): void
    {
        if (DB::table('users')->whereIn('role', ['admin', 'admin_remote', 'admin_yayasan'])->exists()) {
            throw new LogicException('Hapus akun Admin sebelum mengembalikan skema role.');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['guru', 'siswa'])->default('siswa')->change();
        });
    }
};
