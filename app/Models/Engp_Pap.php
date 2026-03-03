<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engp_Pap extends Model
{
    protected $table = 'engp_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Engp_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Engp_Indicator::class, 'program_id');
    }
}



