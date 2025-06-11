<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'admin_id',
    ];
    protected $table = 'organisations';

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public function chatrooms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Chatroom::class);
    }
}
