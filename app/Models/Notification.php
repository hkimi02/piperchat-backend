<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable=[
        'title',
        'body',
        'read',
        'user_id',
        'notification_image',
        'type',
        'redirect'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAllAsRead()
    {
        $this->where('user_id', auth()->id())->update(['read' => true]);
    }

    public function markAsRead(): void
    {
        $this->update(['read' => true]);
    }

    public function deleteAll()
    {
        $this->where('user_id', auth()->id())->delete();
    }

    public function getAllNotifications()
    {
        return $this->where('user_id', auth()->id())->get();
    }

}
