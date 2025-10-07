<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobGroupAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\JobGroupAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'job_group_id',
        'weight_percentage',
    ];

    protected function casts(): array
    {
        return [
            'weight_percentage' => 'decimal:2',
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function jobGroup(): BelongsTo
    {
        return $this->belongsTo(JobGroup::class);
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (JobGroupAssignment $assignment) {
            $assignment->validateWeightPercentage();
        });

        static::updating(function (JobGroupAssignment $assignment) {
            $assignment->validateWeightPercentage();
        });
    }

    public function validateWeightPercentage(): void
    {
        if ($this->weight_percentage < 0 || $this->weight_percentage > 100) {
            throw new \InvalidArgumentException('Weight percentage must be between 0 and 100.');
        }
    }
}
