<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_seen'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value && file_exists('uploads/users/' . $value) ? asset("uploads/users/$value") : 'http://cdn.onlinewebfonts.com/svg/img_568657.png',
        );
    }

    public function messages()
    {
        return $this->belongsToMany(Message::class, 'message_user')->withPivot(['read_at', 'deleted_at']);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')->latest('last_message_id')->withPivot(['joined_at', 'role']);
    }

    public function isOnline()
    {
        return Cache::has('user-is-online-' . $this->id);
    }
}
