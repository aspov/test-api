<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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
        'password' => 'hashed',
    ];

    /**
     * tokens
     */
    public function tokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserToken::class);
    }

    /**
     * generateTokens
     */
    public function generateTokens(): array
    {
        do {
            $aToken = Str::random(40);
        } while (UserToken::where(['token' => $aToken])->exists());

        do {
            $rToken = Str::random(40);
        } while (UserToken::where(['token' => $rToken])->exists());

        UserToken::updateOrCreate(
            ['user_id' => $this->id, 'type' => 'access'],
            ['token' => $aToken, 'expires_at' => now()->addSeconds(UserToken::EXPIRATION_ACEESS_SEC)]);
        UserToken::updateOrCreate(
            ['user_id' => $this->id, 'type' => 'refresh'],
            ['token' => $rToken, 'expires_at' => now()->addSeconds(UserToken::EXPIRATION_REFRESH_SEC)]);

        return ['access_token' => $aToken, 'refresh_token' => $rToken];
    }
}
