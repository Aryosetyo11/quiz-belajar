<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_id', 'student_id', 'started_at', 'submitted_at', 'duration_seconds', 'score', 'essay_scores', 'essay_feedback', 'graded_at', 'graded_by', 'answers', 'question_ids', 'extra_time_minutes', 'access_restorations', 'violation_count', 'violation_logs', 'is_terminated_by_system'])]
class ExamAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'duration_seconds' => 'integer',
            'graded_at' => 'datetime',
            'answers' => 'array',
            'essay_scores' => 'array',
            'essay_feedback' => 'array',
            'question_ids' => 'array',
            'extra_time_minutes' => 'integer',
            'access_restorations' => 'array',
            'violation_count' => 'integer',
            'violation_logs' => 'array',
            'is_terminated_by_system' => 'boolean',
        ];
    }

    public function hasExpired(Exam $exam): bool
    {
        $deadline = $this->started_at->copy()->addMinutes($exam->duration_minutes + (int) $this->extra_time_minutes);

        if (empty($this->access_restorations) && $exam->ends_at !== null && $exam->ends_at->lt($deadline)) {
            $deadline = $exam->ends_at;
        }

        return $deadline->isPast();
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
