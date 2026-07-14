<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    protected $table = 'indicators';

    protected $guarded = [];

    protected $casts = [
        'office_id' => 'array',
    ];
}
