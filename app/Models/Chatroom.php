<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chatroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'organisation_id', 'project_id'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
