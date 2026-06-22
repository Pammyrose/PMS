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
        'subsubactivities',
        'level_6',
        'level_7',
        'level_8',
    ];

    public function physicals()
    {
        return $this->hasMany(Gass_Physical::class, 'programs_id');
    }

    public function indicator()
    {
        return $this->hasOne(Sto_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Sto_Indicator::class, 'program_id');
    }
}
