<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    protected $fillable = ['user_id', 'loan_id', 'settled_by', 'reason', 'days_late', 'amount', 'status', 'receipt_no', 'settled_at'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'days_late' => 'integer',
            'settled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}
