<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nra_Target extends Model
{
    protected $table = 'nra_targets';

    protected $fillable = [
        'user_id',
        'office_ids',
        'values',
        'years',
        'jan', 'feb', 'mar', 'q1',
        'apr', 'may', 'jun', 'q2',
        'jul', 'aug', 'sep', 'q3',
        'oct', 'nov', 'dec', 'q4',
        'annual_total',
    ];

    protected $casts = [
        'years' => 'integer',
        'jan' => 'float', 'feb' => 'float', 'mar' => 'float', 'q1' => 'float',
        'apr' => 'float', 'may' => 'float', 'jun' => 'float', 'q2' => 'float',
        'jul' => 'float', 'aug' => 'float', 'sep' => 'float', 'q3' => 'float',
        'oct' => 'float', 'nov' => 'float', 'dec' => 'float', 'q4' => 'float',
        'annual_total' => 'float',
    ];
}






