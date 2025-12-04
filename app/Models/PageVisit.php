<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    /** @use HasFactory<\Database\Factories\PageVisitFactory> */
    use HasFactory;

    protected $fillable = [
        'session_id',
        'page_url',
        'referrer',
        'ip_address',
        'user_agent',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }
}
