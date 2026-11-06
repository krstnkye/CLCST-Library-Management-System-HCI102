<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookCopy extends Model
{
    public const STATUSES = ['available', 'on_loan', 'on_hold', 'reference', 'maintenance', 'lost'];

    protected $fillable = ['library_book_id', 'barcode', 'copy_code', 'status', 'location'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoan(): HasOne
    {
        return $this->hasOne(Loan::class)->whereNull('returned_at');
    }
}
