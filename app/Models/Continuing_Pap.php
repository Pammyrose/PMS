<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Continuing_Pap extends Model
{
    protected $table = 'continuing_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Continuing_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Continuing_Indicator::class, 'program_id');
    }
}



