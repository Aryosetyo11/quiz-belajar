<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['subject_id', 'type', 'bank_package_id', 'title', 'description', 'duration_minutes', 'question_count', 'essay_question_count', 'starts_at', 'ends_at', 'is_published', 'archived_at', 'archived_by', 'enable_proctoring', 'max_violations'])]
class Exam extends Model
{
    public const TYPE_QUESTION_BANK = 'question_bank';

    public const TYPE_SESSION = 'session';

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_published' => 'boolean',
            'archived_at' => 'datetime',
            'enable_proctoring' => 'boolean',
            'max_violations' => 'integer',
            'question_count' => 'integer',
            'essay_question_count' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function bankPackage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'bank_package_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function sessionsUsingAsBankPackage(): HasMany
    {
        return $this->hasMany(self::class, 'bank_package_id');
    }
}
