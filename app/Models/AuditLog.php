<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'event', 'description', 'ip_address', 'user_agent', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
