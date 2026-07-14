<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
