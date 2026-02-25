<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Gass_Indicator extends Model
{
    protected $table = 'gass_indicators';

    protected $fillable = [
        'name',

        'user_id',
        'program_id',
        'office_id',
    ];

    protected $casts = [
        'office_id' => 'array',
    ];
}