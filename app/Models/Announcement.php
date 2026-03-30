<?php

namespace App\Models;

use App\Enums\AnnouncementTarget;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    protected $fillable = ['user_id', 'club_id', 'section_id', 'title', 'content', 'target_type'];

    protected $casts = [
        'target_type' => AnnouncementTarget::class,
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'announcement_team');
    }

    public function getTargetLabelAttribute(): string
    {
        return match ($this->target_type) {
            AnnouncementTarget::Club => 'Tout le club',
            AnnouncementTarget::Section => $this->section?->sport_type ?? 'Section',
            AnnouncementTarget::Teams => $this->teams->pluck('name')->join(', ') ?: 'Équipe(s)',
        };
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $club = $user->resolveClub();
        if (!$club) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('club_id', $club->id);

        $role = $user->getRoleNames()->first();
        if ($role === Role::Admin->value) {
            return $query;
        }

        $teamIds = $user->teams()->pluck('teams.id');
        $sectionIds = Team::whereIn('id', $teamIds)->pluck('section_id')->unique();

        return $query->where(function (Builder $q) use ($teamIds, $sectionIds) {
            $q->where('target_type', AnnouncementTarget::Club->value)
                ->orWhere(function (Builder $q) use ($sectionIds) {
                    $q->where('target_type', AnnouncementTarget::Section->value)
                        ->whereIn('section_id', $sectionIds);
                })
                ->orWhere(function (Builder $q) use ($teamIds) {
                    $q->where('target_type', AnnouncementTarget::Teams->value)
                        ->whereHas('teams', fn (Builder $q) => $q->whereIn('teams.id', $teamIds));
                });
        });
    }
}
