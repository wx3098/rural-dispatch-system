<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class StatusLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'status_logs';

    protected $fillable = [
        'dispatch_id',
        'status_before',
        'status_after',
        'changed_by_user_id',
        'created_at',
    ];

    //リレーションシップ
    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function chenger(): BelongsTo
    {
        return $this->belongsTo(User::class. 'changed_by_user_id');
    }
}
