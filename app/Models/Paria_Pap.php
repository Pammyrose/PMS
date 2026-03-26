<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paria_Pap extends Model
{
    protected $table = 'paria_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Paria_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Paria_Indicator::class, 'program_id');
    }
}



