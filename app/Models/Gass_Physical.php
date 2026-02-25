<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gass_Physical extends Model
{

protected $table = 'gass_physical';
protected $fillable = [
    'user_id', 'office_id',
    'programs_id', 'performance_indicator', 'target',
    'period_type', 'year',
    'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec',
    'q1','q2','q3','q4',
    'first_half','second_half',
    'annual_total',
    'remarks',
];

    protected $casts = [
        'target' => 'integer',
        'jan' => 'integer',
        'feb' => 'integer',
        'mar' => 'integer',
        'apr' => 'integer',
        'may' => 'integer',
        'jun' => 'integer',
        'jul' => 'integer',
        'aug' => 'integer',
        'sep' => 'integer',
        'oct' => 'integer',
        'nov' => 'integer',
        'dec' => 'integer',
        'year' => 'integer',
    ];

    // Optional: Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class); // if you have Office model
    }

    public function program()
    {
        return $this->belongsTo(Gass_Pap::class, 'programs_id');
    }

    // Helper method example
    public function getTotalAttribute()
    {
        if ($this->period_type === 'monthly') {
            return $this->jan + $this->feb + $this->mar + $this->apr +
                $this->may + $this->jun + $this->jul + $this->aug +
                $this->sep + $this->oct + $this->nov + $this->dec;
        }

        if ($this->period_type === 'quarterly') {
            return $this->q1 + $this->q2 + $this->q3 + $this->q4;
        }

        if ($this->period_type === 'semiannual') {
            return $this->first_half + $this->second_half;
        }

        return $this->annual_total;
    }
}