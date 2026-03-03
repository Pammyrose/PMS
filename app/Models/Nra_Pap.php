<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nra_Pap extends Model
{
    protected $table = 'nra_pap';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function indicator()
    {
        return $this->hasOne(Nra_Indicator::class, 'program_id');
    }

    public function indicators()
    {
        return $this->hasMany(Nra_Indicator::class, 'program_id');
    }
}





