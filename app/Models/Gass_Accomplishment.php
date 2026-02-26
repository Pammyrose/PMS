<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gass_Accomplishment extends Model
{
    protected $table = 'gass_accomplishment';

    protected $fillable = [
        'user_id',
        'office_id',
        'program_id',
        'indicator_id',
        'year',
        'jan', 'feb', 'mar', 'q1',
        'apr', 'may', 'jun', 'q2',
        'jul', 'aug', 'sep', 'q3',
        'oct', 'nov', 'dec', 'q4',
        'annual_total',
    ];

    protected $casts = [
        'year' => 'integer',
        'jan' => 'float', 'feb' => 'float', 'mar' => 'float', 'q1' => 'float',
        'apr' => 'float', 'may' => 'float', 'jun' => 'float', 'q2' => 'float',
        'jul' => 'float', 'aug' => 'float', 'sep' => 'float', 'q3' => 'float',
        'oct' => 'float', 'nov' => 'float', 'dec' => 'float', 'q4' => 'float',
        'annual_total' => 'float',
    ];
}
