<?php

namespace App\Models;

use App\GroupStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobGroup extends Model
{
    /** @use HasFactory<\Database\Factories\JobGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => GroupStatus::class,
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(JobGroupAssignment::class);
    }

    public function jobPostings(): BelongsToMany
    {
        return $this->belongsToMany(JobPosting::class, 'job_group_assignments')
            ->withPivot('weight_percentage')
            ->withTimestamps();
    }

    public function getTotalWeightAttribute(): float
    {
        return $this->assignments()->sum('weight_percentage');
    }

    public function isWeightValid(): bool
    {
        return abs($this->getTotalWeightAttribute() - 100.0) < 0.01;
    }

    public function getJobCountAttribute(): int
    {
        return $this->assignments()->count();
    }
}
