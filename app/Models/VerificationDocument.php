<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VerificationDocument extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public const TYPES = ['license', 'identity', 'tin', 'trade_license', 'permit', 'other'];

    protected $primaryKey = 'document_id';

    protected $fillable = [
        'documentable_type', 'documentable_id', 'uploaded_by', 'document_type',
        'original_name', 'path', 'mime_type', 'size_bytes', 'sha256', 'status',
        'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }
}
