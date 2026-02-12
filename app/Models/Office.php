<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Office extends Model
{
 

    public function children()
    {
        return $this->hasMany(Office::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Office::class, 'parent_id');
    }
}