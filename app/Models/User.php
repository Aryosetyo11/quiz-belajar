<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'nisn', 'role', 'password', 'school_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_TEACHER = 'guru';

    public const ROLE_STUDENT = 'siswa';

    public const ROLE_ADMIN_REMOTE = 'admin_remote';

    public const ROLE_ADMIN_FOUNDATION = 'admin_yayasan';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine the dashboard route for this user's role.
     */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_TEACHER => 'teacher.dashboard',
            self::ROLE_STUDENT => 'student.dashboard',
            self::ROLE_ADMIN_REMOTE => 'remote.dashboard',
            self::ROLE_ADMIN_FOUNDATION => 'foundation.dashboard',
            default => throw new \LogicException('Akun tidak memiliki peran portal yang valid.'),
        };
    }

    public function subjectsAsTeacher(): HasMany
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id')
            ->withTimestamps();
    }

    public function enrolledSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_student', 'student_id', 'subject_id')
            ->withTimestamps();
    }
}
