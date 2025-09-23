<?php

namespace App\Models;

use App\EmploymentType;
use App\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPosting extends Model
{
    /** @use HasFactory<\Database\Factories\JobPostingFactory> */
    use HasFactory;

    protected $fillable = [
        'publisher_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'employment_type',
        'application_url',
        'expiration_date',
        'category',
        'remote_work_option',
        'status',
        'featured',
        'rpa',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'remote_work_option' => 'boolean',
            'featured' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'rpa' => 'decimal:2',
            'employment_type' => EmploymentType::class,
            'status' => JobStatus::class,
        ];
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    public function getCoordinatesAttribute(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }

        return null;
    }
}
