<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id', 'type', 'custom_type', 'title',
        'location', 'start_at', 'end_at', 'description',
    ];

    protected $casts = [
        'type' => EventType::class,
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function convocations(): HasMany
    {
        return $this->hasMany(Convocation::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === EventType::Other
            ? ($this->custom_type ?? 'Autre')
            : $this->type->label();
    }
}
