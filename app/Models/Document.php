<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id', 'club_id', 'type', 'custom_label',
        'season', 'expires_at', 'notes', 'uploaded_by',
    ];

    protected $casts = [
        'type' => DocumentType::class,
        'expires_at' => 'date',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document_file')->singleFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getStatusAttribute(): string
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }
        return 'valid';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'expired' ? 'Expiré' : 'Valide';
    }

    public static function currentSeason(): string
    {
        $year = (int) date('Y');
        $month = (int) date('m');
        return $month >= 8 ? "{$year}-" . ($year + 1) : ($year - 1) . "-{$year}";
    }
}
