<?php

namespace App\Models;

use App\Enums\ConvocationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Convocation extends Model
{
    protected $fillable = ['event_id', 'user_id', 'status'];

    protected $casts = [
        'status' => ConvocationStatus::class,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
