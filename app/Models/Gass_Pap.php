<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gass_Pap extends Model
{
    protected $table = 'gass_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function physicals()
    {
        return $this->hasMany(Gass_Physical::class, 'programs_id');
    }

    public function indicator()
    {
        return $this->hasOne(Gass_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Gass_Indicator::class, 'program_id');
    }
}