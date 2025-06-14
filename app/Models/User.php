<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'role',
        'email',
        'password',
        'is_enabled',
        'verification_pin',
        'email_verified_at',
        'deleted_at',
        'profile_picture',
        'organisation_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $appends = [
        'full_name'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_enabled' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function softDelete(): void
    {
        $this->update([
            'deleted_at' => now(),
            'email'=> $this->email . $this->id .'_deleted'
        ]);
        $this->save();
    }

    public function fcmTokens()
    {
        return $this->hasMany(FCMToken::class);
    }

    public function organisation()
    {
        if($this->role === UserRole::ADMIN->value) {
            return $this->hasOne(Organisation::class, 'admin_id');
        }else{
        return $this->belongsTo(Organisation::class, 'organisation_id');
        }
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function chatrooms(): BelongsToMany
    {
        return $this->belongsToMany(Chatroom::class, 'chatroom_user');
    }



}
