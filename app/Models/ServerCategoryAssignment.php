<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $server_id
 * @property int $user_id
 * @property int $category_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Server $server
 * @property User $user
 * @property ServerCategory $category
 */
class ServerCategoryAssignment extends Model
{
    protected $fillable = [
        'server_id',
        'user_id',
        'category_id',
    ];

    protected $casts = [
        'server_id' => 'integer',
        'user_id' => 'integer',
        'category_id' => 'integer',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServerCategory::class);
    }
}
