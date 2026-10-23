<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_id', 'prompt', 'options', 'answer_key', 'points', 'position'])]
class ExamQuestion extends Model
{
    protected function casts(): array
    {
        return ['options' => 'array'];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
