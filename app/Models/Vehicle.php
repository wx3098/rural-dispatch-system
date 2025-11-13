<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'model_name',
        'driver_id',
        'status',
    ];

    //リレーションシップ

    //担当ドライバー（多対１）
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    //配車依頼（1対多）
    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }
}
