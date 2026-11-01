<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\School;
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

        $school = School::query()->first() ?? School::query()->create([
            'code' => 'DEMO-SCHOOL',
            'name' => 'Sekolah Demo',
            'address' => 'Lingkungan pengembangan',
            'is_active' => true,
        ]);
        $classroom = Classroom::query()->firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Kelas Demo', 'academic_year' => '2026/2027'],
            ['level' => 'Demo'],
        );

        User::updateOrCreate(
            ['email' => 'guru001@yayasan.nclearning'],
            [
                'name' => 'Guru Demo',
                'role' => User::ROLE_TEACHER,
                'school_id' => $school->id,
                'nisn' => null,
                'password' => 'GuruDemo!2026',
            ],
        );

        User::updateOrCreate(
            ['email' => 'remote@yayasan.nclearning'],
            [
                'name' => 'Manajemen All Remote Demo',
                'role' => User::ROLE_ADMIN_REMOTE,
                'nisn' => null,
                'password' => 'AdminDemo!2026',
            ],
        );

        User::updateOrCreate(
            ['email' => 'admin@yayasan.nclearning'],
            [
                'name' => 'Manajemen Yayasan Demo',
                'role' => User::ROLE_ADMIN_FOUNDATION,
                'nisn' => null,
                'password' => 'AdminDemo!2026',
            ],
        );

        User::updateOrCreate(
            ['nisn' => '0000000001'],
            [
                'name' => 'Siswa Demo 1',
                'email' => null,
                'role' => User::ROLE_STUDENT,
                'school_id' => $school->id,
                'password' => 'SiswaDemo01!2026',
            ],
        );

        User::updateOrCreate(
            ['nisn' => '0000000002'],
            [
                'name' => 'Siswa Demo 2',
                'email' => null,
                'role' => User::ROLE_STUDENT,
                'school_id' => $school->id,
                'password' => 'SiswaDemo02!2026',
            ],
        );

        $classroom->students()->syncWithoutDetaching(
            User::query()->whereIn('nisn', ['0000000001', '0000000002'])->pluck('id'),
        );
    }
}
