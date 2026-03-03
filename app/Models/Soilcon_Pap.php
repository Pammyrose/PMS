<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soilcon_Pap extends Model
{
    protected $table = 'soilcon_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Soilcon_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Soilcon_Indicator::class, 'program_id');
    }
}




