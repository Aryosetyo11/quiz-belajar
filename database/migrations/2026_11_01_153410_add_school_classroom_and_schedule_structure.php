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
        Schema::create('schools', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('code', 40)->unique();
            $table->string('address', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('school_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->index(['school_id', 'role']);
        });

        Schema::create('classrooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('academic_year', 20);
            $table->string('level', 40)->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'name', 'academic_year']);
            $table->index(['school_id', 'academic_year']);
        });

        Schema::create('classroom_student', function (Blueprint $table): void {
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['classroom_id', 'student_id']);
            $table->index('student_id');
        });

        Schema::table('subjects', function (Blueprint $table): void {
            $table->foreignId('school_id')->nullable()->after('teacher_id')->constrained()->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->after('school_id')->constrained()->nullOnDelete();
            $table->index(['school_id', 'classroom_id']);
        });

        Schema::create('subject_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('location', 120)->nullable();
            $table->timestamps();
            $table->index(['subject_id', 'day_of_week']);
        });

        $hasSchoolUsers = DB::table('users')->whereIn('role', ['guru', 'siswa'])->exists();
        $hasSubjects = DB::table('subjects')->exists();

        if ($hasSchoolUsers || $hasSubjects) {
            $now = now();
            $schoolId = DB::table('schools')->insertGetId([
                'name' => 'Sekolah Utama',
                'code' => 'SEKOLAH-UTAMA',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('users')
                ->whereIn('role', ['guru', 'siswa'])
                ->update(['school_id' => $schoolId]);

            foreach (DB::table('subjects')->orderBy('id')->get(['id', 'teacher_id', 'name']) as $subject) {
                $classroomId = DB::table('classrooms')->insertGetId([
                    'school_id' => $schoolId,
                    'name' => 'Data awal #'.$subject->id.' - '.mb_substr($subject->name, 0, 75),
                    'academic_year' => '2026/2027',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('subjects')->where('id', $subject->id)->update([
                    'school_id' => $schoolId,
                    'classroom_id' => $classroomId,
                ]);

                $existingStudents = DB::table('subject_student')
                    ->where('subject_id', $subject->id)
                    ->get(['student_id', 'created_at', 'updated_at']);

                foreach ($existingStudents as $enrollment) {
                    DB::table('classroom_student')->insertOrIgnore([
                        'classroom_id' => $classroomId,
                        'student_id' => $enrollment->student_id,
                        'created_at' => $enrollment->created_at ?? $now,
                        'updated_at' => $enrollment->updated_at ?? $now,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_schedules');

        Schema::table('subjects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('classroom_id');
            $table->dropConstrainedForeignId('school_id');
        });

        Schema::dropIfExists('classroom_student');
        Schema::dropIfExists('classrooms');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['school_id', 'role']);
            $table->dropConstrainedForeignId('school_id');
        });

        Schema::dropIfExists('schools');
    }
};
