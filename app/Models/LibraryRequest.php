<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryRequest extends Model
{
    public const TYPES = [
        'book_recommendation' => 'Book recommendation',
        'course_reserve' => 'Course reserve',
        'reading_list' => 'Reading list',
        'instruction_session' => 'Library class session',
        'inter_library_loan' => 'Inter-library loan',
        'academic' => 'Academic request',
        'feedback' => 'Feedback',
        'support' => 'Help request',
    ];

    public const STATUSES = ['submitted', 'in_review', 'approved', 'completed', 'declined'];

    protected $fillable = ['user_id', 'handled_by', 'type', 'subject', 'details', 'status', 'response'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}
