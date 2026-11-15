<?php

namespace App\Models;

use App\Contracts\Models\Identifiable;
use App\Models\Traits\HasRealtimeIdentifier;
use Carbon\Carbon;
use Database\Factories\EggFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * @property int $id
 * @property string $uuid
 * @property string|null $creation_source
 * @property string $creation_source_label
 * @property string|null $catalog_key
 * @property array|null $catalog_origin
 * @property int $nest_id
 * @property string $author
 * @property string $name
 * @property string|null $image_url
 * @property string|null $description
 * @property array|null $features
 * @property string $docker_image -- deprecated, use $docker_images
 * @property array<string, string> $docker_images
 * @property string $update_url
 * @property bool $force_outgoing_ip
 * @property array|null $file_denylist
 * @property string|null $config_files
 * @property string|null $config_startup
 * @property string|null $config_logs
 * @property string|null $config_stop
 * @property int|null $config_from
 * @property string|null $startup
 * @property bool $script_is_privileged
 * @property string|null $script_install
 * @property string $script_entry
 * @property string $script_container
 * @property int|null $copy_script_from
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $copy_script_install
 * @property string $copy_script_entry
 * @property string $copy_script_container
 * @property string|null $inherit_config_files
 * @property string|null $inherit_config_startup
 * @property string|null $inherit_config_logs
 * @property string|null $inherit_config_stop
 * @property array|null $inherit_file_denylist
 * @property array|null $inherit_features
 * @property Nest $nest
 * @property Collection|Server[] $servers
 * @property Collection|EggVariable[] $variables
 * @property Egg|null $scriptFrom
 * @property Egg|null $configFrom
 */
#[Attributes\Identifiable('eegg')]
class Egg extends Model implements Identifiable
{
    /** @use HasFactory<EggFactory> */
    use HasFactory;

    use HasRealtimeIdentifier;

    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'egg';

    /**
     * Every egg version the panel can import sorted by newest first.
     * Add to this list to release a new egg version.
     */
    public const VERSIONS = ['RCYL_v26', 'PTDL_v2', 'PTDL_v1'];

    /**
     * Defines the current egg export version.
     */
    public const EXPORT_VERSION = self::VERSIONS[0];

    /**
     * Different features that can be enabled on any given egg. These are used internally
     * to determine which types of frontend functionality should be shown to the user. Eggs
     * will automatically inherit features from a parent egg if they are already configured
     * to copy configuration values from said egg.
     *
     * To skip copying the features, an empty array value should be passed in ("[]") rather
     * than leaving it null.
     */
    public const FEATURE_EULA_POPUP = 'eula';

    public const FEATURE_FASTDL = 'fastdl';

    /**
     * The table associated with the model.
     */
    protected $table = 'eggs';

    /**
     * Fields that are not mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'features',
        'docker_images',
        'force_outgoing_ip',
        'file_denylist',
        'config_files',
        'config_startup',
        'config_logs',
        'config_stop',
        'config_from',
        'startup',
        'script_is_privileged',
        'script_install',
        'script_entry',
        'script_container',
        'copy_script_from',
        'image',
    ];

    /**
     * Cast values to correct type.
     */
    protected $casts = [
        'catalog_origin' => 'array',
        'nest_id' => 'integer',
        'config_from' => 'integer',
        'script_is_privileged' => 'boolean',
        'force_outgoing_ip' => 'boolean',
        'copy_script_from' => 'integer',
        'features' => 'array',
        'docker_images' => 'array',
        'file_denylist' => 'array',
    ];

    public static array $validationRules = [
        'nest_id' => 'required|bail|numeric|exists:nests,id',
        'uuid' => 'required|string|size:36',
        'name' => 'required|string|max:191',
        'description' => 'string|nullable',
        'features' => 'array|nullable',
        'author' => 'required|string|email',
        'file_denylist' => 'array|nullable',
        'file_denylist.*' => 'string',
        'docker_images' => 'required|array|min:1',
        'docker_images.*' => ['required', 'string', 'max:191', 'regex:/^[\w#\.\/\- ]*\|?~?[\w\.\/\-:@ ]*$/'],
        'startup' => 'required|nullable|string',
        'config_from' => 'sometimes|bail|nullable|numeric|exists:eggs,id',
        'config_stop' => 'required_without:config_from|nullable|string|max:191',
        'config_startup' => 'required_without:config_from|nullable|json',
        'config_logs' => 'required_without:config_from|nullable|json',
        'config_files' => 'required_without:config_from|nullable|json',
        'update_url' => 'sometimes|nullable|string',
        'force_outgoing_ip' => 'sometimes|boolean',
        'image' => 'string|nullable',
    ];

    protected $attributes = [
        'features' => null,
        'file_denylist' => null,
        'config_stop' => null,
        'config_startup' => null,
        'config_logs' => null,
        'config_files' => null,
        'update_url' => null,
        'image' => null,
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! str_starts_with($this->image ?? '', 'data:image/')) {
            return $this->image;
        }

        return route('egg-image', ['egg' => $this->uuid, 'version' => hash('xxh128', $this->image)]);
    }

    public function getCreationSourceLabelAttribute(): string
    {
        $provider = $this->catalog_origin['provider'] ?? null;
        if (in_array($provider, ['Pterodactyl', 'Rextstore'], true)) {
            return $provider;
        }

        return trans('admin/catalog.sources.'.(in_array($this->creation_source, ['Imported', 'Created'], true) ? strtolower($this->creation_source) : 'unknown'));
    }

    public function validate(): void
    {
        parent::validate();
        if ($this->catalog_key !== null && $this->isDirty('nest_id') && static::query()
            ->where('nest_id', $this->nest_id)->where('catalog_key', $this->catalog_key)
            ->when($this->exists, fn ($query) => $query->where('id', '!=', $this->id))->exists()) {
            throw ValidationException::withMessages([
                'nest_id' => trans('admin/catalog.errors.move_conflict'),
            ]);
        }
    }

    /**
     * Returns the install script for the egg; if egg is copying from another
     * it will return the copied script.
     */
    public function getCopyScriptInstallAttribute(): ?string
    {
        if (! is_null($this->script_install) || is_null($this->copy_script_from)) {
            return $this->script_install;
        }

        return $this->scriptFrom->script_install;
    }

    /**
     * Returns the entry command for the egg; if egg is copying from another
     * it will return the copied entry command.
     */
    public function getCopyScriptEntryAttribute(): string
    {
        if (is_null($this->copy_script_from)) {
            return $this->script_entry;
        }

        return $this->scriptFrom->script_entry;
    }

    /**
     * Returns the install container for the egg; if egg is copying from another
     * it will return the copied install container.
     */
    public function getCopyScriptContainerAttribute(): string
    {
        if (is_null($this->copy_script_from)) {
            return $this->script_container;
        }

        return $this->scriptFrom->script_container;
    }

    /**
     * Return the file configuration for an egg.
     */
    public function getInheritConfigFilesAttribute(): ?string
    {
        return $this->resolveInheritedAttribute('config_files');
    }

    /**
     * Return the startup configuration for an egg.
     */
    public function getInheritConfigStartupAttribute(): ?string
    {
        return $this->resolveInheritedAttribute('config_startup');
    }

    /**
     * Return the log reading configuration for an egg.
     */
    public function getInheritConfigLogsAttribute(): ?string
    {
        return $this->resolveInheritedAttribute('config_logs');
    }

    /**
     * Return the stop command configuration for an egg.
     */
    public function getInheritConfigStopAttribute(): ?string
    {
        return $this->resolveInheritedAttribute('config_stop');
    }

    /**
     * Returns the features available to this egg from the parent configuration if there are
     * no features defined for this egg specifically and there is a parent egg configured.
     */
    public function getInheritFeaturesAttribute(): ?array
    {
        return $this->resolveInheritedAttribute('features');
    }

    /**
     * Returns the features available to this egg from the parent configuration if there are
     * no features defined for this egg specifically and there is a parent egg configured.
     */
    public function getInheritFileDenylistAttribute(): ?array
    {
        return $this->resolveInheritedAttribute('file_denylist', true);
    }

    private function resolveInheritedAttribute(string $attribute, bool $alwaysInherit = false): mixed
    {
        $egg = $this;
        $visited = [];

        while (! is_null($egg->config_from) && ($alwaysInherit || is_null($egg->{$attribute}))) {
            $key = $egg->id === null ? 'object:'.spl_object_id($egg) : 'id:'.$egg->id;
            if (isset($visited[$key])) {
                throw new LogicException('Egg configuration inheritance contains a cycle.');
            }

            $visited[$key] = true;
            $egg = $egg->configFrom;
            if ($egg === null) {
                throw new LogicException('Egg configuration parent does not exist.');
            }
        }

        return $egg->{$attribute};
    }

    /**
     * Gets nest associated with an egg.
     *
     * @return BelongsTo<Nest, $this>
     */
    public function nest(): BelongsTo
    {
        return $this->belongsTo(Nest::class);
    }

    /**
     * Gets all servers associated with this egg.
     *
     * @return HasMany<Server, $this>
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class, 'egg_id');
    }

    /**
     * Gets all variables associated with this egg.
     *
     * @return HasMany<EggVariable, $this>
     */
    public function variables(): HasMany
    {
        return $this->hasMany(EggVariable::class, 'egg_id')->orderBy('id');
    }

    /**
     * Get the parent egg from which to copy scripts.
     *
     * @return BelongsTo<self, $this>
     */
    public function scriptFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'copy_script_from');
    }

    /**
     * Get the parent egg from which to copy configuration settings.
     *
     * @return BelongsTo<self, $this>
     */
    public function configFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'config_from');
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
