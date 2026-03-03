<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enf_Pap extends Model
{
    protected $table = 'enf_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Enf_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Enf_Indicator::class, 'program_id');
    }
}

