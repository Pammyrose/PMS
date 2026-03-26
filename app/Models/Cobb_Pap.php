<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cobb_Pap extends Model
{
    protected $table = 'cobb_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Cobb_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Cobb_Indicator::class, 'program_id');
    }
}



