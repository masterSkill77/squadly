<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['section_id', 'name', 'age_category', 'season'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')->withTimestamps();
    }

    public function players(): BelongsToMany
    {
        return $this->members()->role(Role::Membre->value);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
