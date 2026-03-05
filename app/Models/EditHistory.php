<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'module',
        'edited_part',
        'action',
        'route_name',
        'http_method',
        'record_identifier',
        'changed_fields',
        'request_snapshot',
    ];

    protected $casts = [
        'changed_fields' => 'array',
        'request_snapshot' => 'array',
    ];
}
