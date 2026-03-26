<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $identifier
 * @property string $name
 * @property string $version
 * @property string|null $description
 * @property string|null $author
 * @property string|null $website
 * @property string|null $update_url
 * @property string|null $api_version
 * @property string|null $target_version
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon|null $installed_at
 * @property \Illuminate\Support\Carbon|null $extension_updated_at
 * @property string $install_path
 * @property array<string, mixed>|null $manifest
 */
class Extension extends Model
{
    protected $table = 'extensions';

    protected $fillable = [
        'identifier',
        'name',
        'version',
        'description',
        'author',
        'website',
        'update_url',
        'api_version',
        'target_version',
        'enabled',
        'installed_at',
        'extension_updated_at',
        'manifest',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'installed_at' => 'datetime',
        'extension_updated_at' => 'datetime',
        'manifest' => 'array',
    ];

    public static array $validationRules = [
        'identifier' => 'required|string|max:191|regex:/^[a-z0-9][a-z0-9-_\.]*$/i|unique:extensions,identifier',
        'name' => 'required|string|max:191',
        'version' => 'required|string|max:191',
        'enabled' => 'boolean',
    ];

    public function getInstallPathAttribute(): string
    {
        $identifier = (string) ($this->attributes['identifier'] ?? '');

        return base_path('extensions/' . $identifier);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
