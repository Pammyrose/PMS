<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccomplishmentSubmission extends Model
{
    protected $fillable = [
        'submission_type',
        'sector',
        'user_id',
        'office_id',
        'penro_office_id',
        'program_id',
        'row_id',
        'indicator_id',
        'year',
        'payload',
        'request_reason',
        'status',
        'reviewed_by',
        'review_notes',
        'reviewed_at',
        'user_read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'reviewed_at' => 'datetime',
        'user_read_at' => 'datetime',
    ];

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Ppa::class, 'program_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }
}
