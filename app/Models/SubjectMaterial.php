<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'description', 'file_path', 'original_file_name', 'file_size'])]
class SubjectMaterial extends Model
{
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
