<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'organisation_id'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function chatroom()
    {
        return $this->hasOne(Chatroom::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
