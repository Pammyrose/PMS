<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pap extends Model
{
    protected $table = 'pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
        'target',
        'budget',
        'indicators',
        'start_date',
        'end_date',
        'office_id',
        'div',
    ];

    protected $casts = [
        'start_date'           => 'date:Y-m-d',
        'end_date'             => 'date:Y-m-d',
        'budget'               => 'decimal:2',
        'target'               => 'integer',
        'div'                  => 'array',
    ];
}