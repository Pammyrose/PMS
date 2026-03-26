<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cobb_Indicator extends Model
{
    protected $table = 'cobb_indicators';

    protected $fillable = [
        'name',
        'indicator_type',
        'user_id',
        'program_id',
        'office_id',
    ];

    protected $casts = [
        'office_id' => 'array',
    ];
}



