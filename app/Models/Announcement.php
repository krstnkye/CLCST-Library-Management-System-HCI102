<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    public const AUDIENCES = ['all' => 'Everyone', 'student' => 'Students', 'faculty' => 'Faculty'];

    protected $fillable = ['user_id', 'title', 'body', 'audience', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeVisibleTo(Builder $query, string $role): void
    {
        $query->where('published_at', '<=', now())->whereIn('audience', ['all', $role]);
    }
}
