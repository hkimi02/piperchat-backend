<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FCMToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'fcm_token'];
    protected $table='fcm_tokens';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
