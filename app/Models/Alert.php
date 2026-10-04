<?php

namespace App\Models;

use Database\Factories\AlertFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property bool $enabled
 * @property string $type
 * @property string|null $title
 * @property string $message
 * @property string|null $color
 * @property array<int, array{label: string, url: string, style?: string, new_tab?: bool}>|null $buttons
 * @property bool $dismissible
 * @property bool $dismiss_on_action
 * @property int|null $redisplay_after_days
 * @property string[] $placements
 * @property string $audience
 * @property array<string, mixed>|null $targeting
 * @property int $priority
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $dismissals_reset_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|AlertInteraction[] $interactions
 *
 * @method static Builder|Alert active()
 */
class Alert extends Model
{
    /** @use HasFactory<AlertFactory> */
    use HasFactory;

    public const RESOURCE_NAME = 'alert';

    public const TYPES = ['info', 'announcement', 'success', 'warning', 'danger'];

    public const PLACEMENT_DASHBOARD = 'dashboard';

    public const PLACEMENT_SERVER = 'server';

    public const PLACEMENT_ACCOUNT = 'account';

    public const PLACEMENT_AUTH = 'auth';

    public const PLACEMENTS = [
        self::PLACEMENT_DASHBOARD,
        self::PLACEMENT_SERVER,
        self::PLACEMENT_ACCOUNT,
        self::PLACEMENT_AUTH,
    ];

    public const AUDIENCES = ['everyone', 'admins', 'users'];

    public const BUTTON_STYLES = ['primary', 'secondary'];

    public const TWO_FACTOR_STATES = ['any', 'enabled', 'disabled'];

    public const SERVER_RELATIONS = ['any', 'has', 'owner', 'subuser', 'none'];

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_DISABLED = 'disabled';

    public const MAX_BUTTONS = 3;

    public const BUTTON_URL_PATTERN = '/^(https?:\/\/|mailto:|\/(?!\/))/i';

    protected $table = 'alerts';

    protected $guarded = ['id', 'uuid', 'created_at', 'updated_at'];

    protected $attributes = [
        'enabled' => true,
        'type' => 'info',
        'dismissible' => true,
        'dismiss_on_action' => false,
        'audience' => 'everyone',
        'priority' => 0,
    ];

    protected $casts = [
        'enabled' => 'bool',
        'buttons' => 'array',
        'dismissible' => 'bool',
        'dismiss_on_action' => 'bool',
        'redisplay_after_days' => 'int',
        'placements' => 'array',
        'targeting' => 'array',
        'priority' => 'int',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'dismissals_reset_at' => 'datetime',
    ];

    public static array $validationRules = [
        'name' => ['required', 'string', 'max:191'],
        'enabled' => ['boolean'],
        'type' => ['required', 'string', 'in:info,announcement,success,warning,danger'],
        'title' => ['nullable', 'string', 'max:191'],
        'message' => ['required', 'string', 'max:65535'],
        'color' => ['nullable', 'string', 'regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'],
        'buttons' => ['nullable', 'array', 'max:3'],
        'buttons.*.label' => ['required', 'string', 'max:48'],
        'buttons.*.url' => ['required', 'string', 'max:2048', 'regex:'.self::BUTTON_URL_PATTERN],
        'buttons.*.style' => ['nullable', 'string', 'in:primary,secondary'],
        'buttons.*.new_tab' => ['nullable', 'boolean'],
        'dismissible' => ['boolean'],
        'dismiss_on_action' => ['boolean'],
        'redisplay_after_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        'placements' => ['required', 'array', 'min:1'],
        'placements.*' => ['string', 'distinct', 'in:dashboard,server,account,auth'],
        'audience' => ['required', 'string', 'in:everyone,admins,users'],
        'targeting' => ['nullable', 'array'],
        'priority' => ['integer', 'between:-1000,1000'],
        'starts_at' => ['nullable', 'date'],
        'ends_at' => ['nullable', 'date'],
        'dismissals_reset_at' => ['nullable', 'date'],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Alert $alert) {
            $alert->uuid ??= Str::uuid()->toString();
        });

        static::deleting(function (Alert $alert) {
            $alert->interactions()->delete();
        });
    }

    /**
     * @param  Builder<Alert>|Relation<Alert, Model, mixed>  $query
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): Builder|Relation
    {
        if (($field ?? $this->getRouteKeyName()) === 'uuid' && ! Str::isUuid($value)) {
            return $query->whereRaw('1 = 0');
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    /**
     * @return HasMany<AlertInteraction, $this>
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(AlertInteraction::class);
    }

    /**
     * @param  Builder<Alert>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $now = Carbon::now();

        $query->where('enabled', true)
            ->where(fn (Builder $builder) => $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $builder) => $builder->whereNull('ends_at')->orWhere('ends_at', '>', $now));
    }

    public function status(): string
    {
        if (! $this->enabled) {
            return self::STATUS_DISABLED;
        }

        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            return self::STATUS_EXPIRED;
        }

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return self::STATUS_SCHEDULED;
        }

        return self::STATUS_ACTIVE;
    }

    public function isHiddenBy(?AlertInteraction $interaction): bool
    {
        if ($interaction === null) {
            return false;
        }

        return ($this->dismissible && $this->hidesSince($interaction->dismissed_at))
            || ($this->hidesAfterAction() && $this->hidesSince($interaction->clicked_at));
    }

    public function hidesAfterAction(): bool
    {
        return $this->dismiss_on_action && ! empty($this->publicButtons());
    }

    public function hasAudienceRules(): bool
    {
        if ($this->audience !== 'everyone') {
            return true;
        }

        foreach (['users', 'languages', 'nodes', 'locations', 'nests', 'eggs'] as $list) {
            if (! empty($this->targetList($list))) {
                return true;
            }
        }

        return $this->targetValue('two_factor', 'any') !== 'any'
            || $this->targetValue('servers', 'any') !== 'any'
            || $this->targetValue('account_age_min') !== null
            || $this->targetValue('account_age_max') !== null;
    }

    private function hidesSince(?Carbon $at): bool
    {
        if ($at === null) {
            return false;
        }

        if ($this->dismissals_reset_at !== null && $at->lessThanOrEqualTo($this->dismissals_reset_at)) {
            return false;
        }

        return $this->redisplay_after_days === null
            || $at->copy()->addDays($this->redisplay_after_days)->isFuture();
    }

    public function hasPlacement(string $placement): bool
    {
        return in_array($placement, $this->placements ?? [], true);
    }

    /**
     * @return array<int, int|string>
     */
    public function targetList(string $key): array
    {
        $values = $this->targeting[$key] ?? [];

        return is_array($values) ? array_values(array_filter($values, fn ($value) => $value !== null && $value !== '')) : [];
    }

    public function targetValue(string $key, mixed $default = null): mixed
    {
        $value = $this->targeting[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    /**
     * @return array<int, array{label: string, url: string, style: string, new_tab: bool}>
     */
    public function publicButtons(): array
    {
        $buttons = [];

        foreach ($this->buttons ?? [] as $button) {
            $label = trim((string) ($button['label'] ?? ''));
            $url = trim((string) ($button['url'] ?? ''));

            if ($label === '' || ! preg_match(self::BUTTON_URL_PATTERN, $url)) {
                continue;
            }

            $style = (string) ($button['style'] ?? 'primary');

            $buttons[] = [
                'label' => $label,
                'url' => $url,
                'style' => in_array($style, self::BUTTON_STYLES, true) ? $style : 'primary',
                'new_tab' => (bool) ($button['new_tab'] ?? false),
            ];
        }

        return array_slice($buttons, 0, self::MAX_BUTTONS);
    }
}
