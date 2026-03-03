<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pa_Pap extends Model
{
    protected $table = 'pa_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Pa_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Pa_Indicator::class, 'program_id');
    }
}


