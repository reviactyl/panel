<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SubuserPreviewSession extends Model
{
    protected $table = 'subuser_preview_sessions';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'owner_id' => 'int',
        'server_id' => 'int',
        'subuser_id' => 'int',
        'state' => 'array',
        'last_seen_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static array $validationRules = [
        'uuid' => 'required|uuid|unique:subuser_preview_sessions,uuid',
        'owner_id' => 'required|integer|unique:subuser_preview_sessions,owner_id|exists:users,id',
        'server_id' => 'required|integer|exists:servers,id',
        'subuser_id' => 'required|integer|exists:subusers,id',
        'token_hash' => 'required|string|size:64',
        'state' => 'nullable|array',
        'last_seen_at' => 'required|date',
        'expires_at' => 'required|date',
    ];

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<Server, $this> */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /** @return BelongsTo<Subuser, $this> */
    public function subuser(): BelongsTo
    {
        return $this->belongsTo(Subuser::class);
    }

    public function tokenMatches(string $token): bool
    {
        return hash_equals($this->token_hash, hash('sha256', $token));
    }

    public function renew(): void
    {
        $now = Carbon::now();

        $this->forceFill([
            'last_seen_at' => $now,
            'expires_at' => $now->clone()->addMinutes(30),
        ])->save();
    }
}
