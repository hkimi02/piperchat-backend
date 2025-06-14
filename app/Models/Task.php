<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'project_id', 'user_id','due_date', 'priority', 'tags'];
    protected $appends = ['project_name'];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProjectNameAttribute()
    {
        return $this->project ? $this->project->name : null;
    }
}
