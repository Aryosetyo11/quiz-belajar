<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

class DemoPortalUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Dummy portal accounts may only be seeded locally or in testing.');
        }

        User::firstOrCreate(
            ['email' => 'guru001@yayasan.nclearning'],
            [
                'name' => 'Guru Demo',
                'role' => User::ROLE_TEACHER,
                'nisn' => null,
                'password' => 'GuruDemo!2026',
            ],
        );

        User::firstOrCreate(
            ['nisn' => '0000000001'],
            [
                'name' => 'Siswa Demo 1',
                'email' => null,
                'role' => User::ROLE_STUDENT,
                'password' => 'SiswaDemo01!2026',
            ],
        );

        User::firstOrCreate(
            ['nisn' => '0000000002'],
            [
                'name' => 'Siswa Demo 2',
                'email' => null,
                'role' => User::ROLE_STUDENT,
                'password' => 'SiswaDemo02!2026',
            ],
        );
    }
}
