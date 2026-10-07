<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $alert_id
 * @property int $user_id
 * @property Carbon|null $dismissed_at
 * @property Carbon|null $clicked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Alert $alert
 * @property User $user
 */
class AlertInteraction extends Model
{
    protected $table = 'alert_interactions';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'alert_id' => 'int',
        'user_id' => 'int',
        'dismissed_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public static array $validationRules = [
        'alert_id' => 'required|integer|exists:alerts,id',
        'user_id' => 'required|integer|exists:users,id',
        'dismissed_at' => 'nullable|date',
        'clicked_at' => 'nullable|date',
    ];

    /**
     * @return BelongsTo<Alert, $this>
     */
    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
