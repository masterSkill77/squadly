<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'phase_id', 'home_club_id', 'away_club_id',
        'scheduled_at', 'location', 'status',
        'home_score', 'away_score', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
            'scheduled_at' => 'datetime',
        ];
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function homeClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'home_club_id');
    }

    public function awayClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'away_club_id');
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerStat::class);
    }

    public function getScoreLabelAttribute(): string
    {
        if ($this->home_score === null || $this->away_score === null) {
            return '— : —';
        }

        return "{$this->home_score} : {$this->away_score}";
    }
}
