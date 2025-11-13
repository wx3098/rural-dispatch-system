<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'current_lat',
        'current_lng',
        'phone_number',
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
        ];
    }

    //リレーションシップ

    //ドライバーとして担当する車両（1対多）
    public function vehcles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'driver_id');
    }

    //利用者として作成した配車依頼（1対多）
    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class, 'user_id');
    }   

    //状態変更を行った履歴（1対多）
    public function statusLogs(): HasMany
    {
        return $this->hasMany(StatusLog::class, 'changed_by_user_id');
    }
}
