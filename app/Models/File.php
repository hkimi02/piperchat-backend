<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\URL;

class File extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['url'];

    protected $fillable = [
        'name',
        'path',
        'size',
        'type',
        'user_id',
        'project_id',
        'message_id',
    ];

    /**
     * Get the user that owns the file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that the file belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the message that the file belongs to.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the file's URL.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => URL::temporarySignedRoute(
                'files.show',
                now()->addMinutes(5),
                [
                    'projectId' => $this->project_id,
                    'filename' => basename($this->path)
                ]
            )
        );
    }
}
