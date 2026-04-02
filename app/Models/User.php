<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'has_completed_onboarding'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function club(): HasOne
    {
        return $this->hasOne(Club::class, 'owner_id');
    }

    public function hasClub(): bool
    {
        return $this->club()->exists();
    }

    /**
     * Resolve the club this user belongs to, regardless of role.
     * Admin → owns the club. Coach/Membre → via their MemberProfile.
     */
    public function resolveClub(): ?Club
    {
        return $this->club ?? $this->memberProfiles()->first()?->club;
    }

    public function memberProfiles(): HasMany
    {
        return $this->hasMany(MemberProfile::class);
    }

    public function sectionProfiles(): HasMany
    {
        return $this->hasMany(MemberSectionProfile::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')->withTimestamps();
    }

    public function organizers(): BelongsToMany
    {
        return $this->belongsToMany(Organizer::class, 'organizer_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function resolveOrganizer(): ?Organizer
    {
        return $this->organizers()->first();
    }

    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerStat::class);
    }
}
