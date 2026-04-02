<?php

namespace App\Models;

use App\Enums\CompetitionFormat;
use App\Enums\CompetitionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id', 'name', 'sport_type', 'season',
        'format', 'status', 'description', 'rules',
        'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'format' => CompetitionFormat::class,
            'status' => CompetitionStatus::class,
            'rules' => 'array',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class)->orderBy('order');
    }

    public function competitionClubs(): HasMany
    {
        return $this->hasMany(CompetitionClub::class);
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'competition_clubs')
            ->withPivot('phase_id', 'status', 'registered_at', 'approved_at')
            ->withTimestamps();
    }

    public function games(): HasManyThrough
    {
        return $this->hasManyThrough(Game::class, Phase::class);
    }

    public function getPointsForWinAttribute(): int
    {
        return $this->rules['points_win'] ?? 3;
    }

    public function getPointsForDrawAttribute(): int
    {
        return $this->rules['points_draw'] ?? 1;
    }

    public function getPointsForLossAttribute(): int
    {
        return $this->rules['points_loss'] ?? 0;
    }
}
