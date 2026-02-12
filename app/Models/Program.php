<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'programs';

    protected $fillable = [
        'title',
        'program',
        'project',
        'activities',
        'subactivities',
    ];

    public function physicals()
    {
        return $this->hasMany(Physical::class);
    }

    public function indicator()
{
    return $this->hasOne(Indicator::class, 'program_id');  // assuming you add program_id to indicators
    // or belongsTo if inverse
}
}