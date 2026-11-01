<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['teacher_id', 'school_id', 'classroom_id', 'name', 'description'])]
class Subject extends Model
{
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subject_student', 'subject_id', 'student_id')
            ->withTimestamps();
    }

    public function materials(): HasMany
    {
        return $this->hasMany(SubjectMaterial::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SubjectSchedule::class)->orderBy('day_of_week')->orderBy('starts_at');
    }
}
