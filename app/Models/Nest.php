<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\NestFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property string $author
 * @property string $name
 * @property string|null $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection|Server[] $servers
 * @property Collection|Egg[] $eggs
 */
class Nest extends Model
{
    /** @use HasFactory<NestFactory> */
    use HasFactory;

    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'nest';

    /**
     * The table associated with the model.
     */
    protected $table = 'nests';

    /**
     * Fields that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'image',
    ];

    public static array $validationRules = [
        'author' => 'required|string|email',
        'name' => 'required|string|max:191',
        'description' => 'nullable|string',
        'image' => 'string|nullable',
    ];

    /**
     * Gets all eggs associated with this service.
     *
     * @return HasMany<Egg, $this>
     */
    public function eggs(): HasMany
    {
        return $this->hasMany(Egg::class);
    }

    /**
     * Gets all servers associated with this nest.
     *
     * @return HasMany<Server, $this>
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
