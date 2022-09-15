<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    protected function lastSeen(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    public function messages()
    {
        return $this->belongsToMany(Message::class, 'message_user')->withPivot(['read_at', 'deleted_at']);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')->latest('last_message_id')->withPivot(['joined_at', 'role'])->with('lastMessage');
    }

    public function isOnline()
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    public function scopeExceptAuth($query)
    {
        return $query->where('id', '<>', auth()->id());
    }

    public function scopeSearch($query)
    {
        return $query->when(request('search'), function($query) {
                        $query->where('name', 'LIKE', '%'.request('search').'%')->orWhere('email', 'LIKE', '%'.request('search').'%');
                    });
    }

    public function scopeHasConversationWithAuth($query)
    {
        return $query->whereHas('conversations', function($query) {
                            $query->whereHas('users', function($query) {
                                $query->where('user_id', auth()->id());
                            });
                        });
    }

    protected static function boot()
    {
        parent::boot();

        // static::addGlobalScope('order', function (Builder $builder) {
        //     $builder->orderBy('updated_at', 'DESC');
        // });
    }
}
