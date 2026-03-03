<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lands_Pap extends Model
{
    protected $table = 'lands_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Lands_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Lands_Indicator::class, 'program_id');
    }
}



