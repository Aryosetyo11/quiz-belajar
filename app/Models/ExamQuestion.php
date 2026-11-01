<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_id', 'type', 'prompt', 'options', 'answer_key', 'sample_answer', 'points', 'position'])]
class ExamQuestion extends Model
{
    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TYPE_ESSAY = 'essay';

    protected function casts(): array
    {
        return ['options' => 'array'];
    }

    public function isMultipleChoice(): bool
    {
        return ($this->type ?? self::TYPE_MULTIPLE_CHOICE) === self::TYPE_MULTIPLE_CHOICE;
    }

    public function isEssay(): bool
    {
        return $this->type === self::TYPE_ESSAY;
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
