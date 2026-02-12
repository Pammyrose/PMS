<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Indicator extends Model
{
    protected $table = 'indicators';
    protected $fillable = [
        'name',
        'target',
        'budget',
        'deadline',
        'user_id',
        'program_id',
        'office_id',
    ];

    protected $casts = [
        'deadline' => 'date',
        'budget' => 'decimal:2',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}