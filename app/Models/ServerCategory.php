<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $priority
 * @property int|null $parent_id
 * @property ServerCategory|null $parent
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ServerCategory extends Model
{
    use HasFactory;

    public const RESOURCE_NAME = 'server_category';

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'description',
        'color',
        'position',
        'parent_id',
        'priority',
    ];

    protected $casts = ['priority' => 'integer', 'parent_id' => 'integer'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ServerCategoryAssignment::class, 'category_id');
    }
}
