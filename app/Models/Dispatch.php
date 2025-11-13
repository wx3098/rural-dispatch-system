<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'destination_id',
        'requested_pickup_datetime',
        'schedule_arrival_datetime',
        'actual_completed_at',
    ];

    //リレーションシップ
    // 利用者（多対１）
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 車両（多対１）
    public function vehicle(): BelongsTo
    {
       return $this->belongsTo(Vehicle::class);
    }

    // 目的地（多対１）
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    // 状態履歴（1対多）
    public function status_logs(): HasMany
    {
        return $this->hasMany(StatusLog::class);
    }


}
