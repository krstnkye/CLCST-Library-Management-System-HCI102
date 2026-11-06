<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'event', 'description', 'ip_address', 'user_agent', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(?User $actor, string $event, string $description, array $metadata = []): self
    {
        $request = request();

        return static::create([
            'user_id' => $actor?->id,
            'event' => $event,
            'description' => mb_substr($description, 0, 255),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => $metadata,
        ]);
    }
}
