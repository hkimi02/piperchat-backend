<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinCode extends Model
{
    protected $fillable = [
        'code',
        'organisation_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }
}
