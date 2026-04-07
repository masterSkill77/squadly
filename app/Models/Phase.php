<?php

namespace App\Models;

use App\Enums\PhaseStatus;
use App\Enums\PhaseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phase extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id', 'name', 'type', 'order', 'status',
        'qualify_count', 'source_phase_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => PhaseType::class,
            'status' => PhaseStatus::class,
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function sourcePhase(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_phase_id');
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class)->orderByDesc('points')->orderByDesc('goals_for');
    }

    public function competitionClubs(): HasMany
    {
        return $this->hasMany(CompetitionClub::class);
    }
}
