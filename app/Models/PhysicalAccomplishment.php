<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalAccomplishment extends Model
{
    protected $fillable = [
        'sector',
        'user_id',
        'office_id',
        'program_id',
        'row_id',
        'indicator_id',
        'year',
        'jan', 'feb', 'mar', 'q1',
        'apr', 'may', 'jun', 'q2',
        'jul', 'aug', 'sep', 'q3',
        'oct', 'nov', 'dec', 'q4',
        'annual_total',
        'car_totals',
        'group_totals',
        'remarks',
        'imported_from',
    ];

    protected $casts = [
        'year' => 'integer',
        'jan' => 'float', 'feb' => 'float', 'mar' => 'float', 'q1' => 'float',
        'apr' => 'float', 'may' => 'float', 'jun' => 'float', 'q2' => 'float',
        'jul' => 'float', 'aug' => 'float', 'sep' => 'float', 'q3' => 'float',
        'oct' => 'float', 'nov' => 'float', 'dec' => 'float', 'q4' => 'float',
        'annual_total' => 'float',
        'car_totals' => 'array',
        'group_totals' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Ppa::class, 'program_id');
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(Ppa::class, 'row_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }
}
