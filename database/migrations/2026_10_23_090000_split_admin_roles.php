<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'admin')->update(['role' => 'admin_yayasan']);
    }

    public function down(): void
    {
        DB::table('users')->whereIn('role', ['admin_remote', 'admin_yayasan'])->update(['role' => 'admin']);
    }
};
