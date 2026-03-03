<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sto_Pap extends Model
{
    protected $table = 'sto_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Sto_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Sto_Indicator::class, 'program_id');
    }
}
