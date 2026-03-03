<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pa_Accomplishment extends Model
{
    protected $table = 'pa_accomplishment';

    protected $fillable = [
        'user_id',
        'office_id',
        'program_id',
        'indicator_id',
        'year',
        'jan',
        'feb',
        'mar',
        'q1',
        'apr',
        'may',
        'jun',
        'q2',
        'jul',
        'aug',
        'sep',
        'q3',
        'oct',
        'nov',
        'dec',
        'q4',
        'annual_total',
    ];
}


