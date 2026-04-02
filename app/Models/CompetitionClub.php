<?php

namespace App\Models;

use App\Enums\CompetitionClubStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionClub extends Model
{
    protected $fillable = [
        'competition_id', 'club_id', 'team_id', 'phase_id',
        'status', 'registered_at', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CompetitionClubStatus::class,
            'registered_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }
}
