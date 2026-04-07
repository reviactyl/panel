<?php

namespace App\Models;

use App\Contracts\Models\Identifiable;
use App\Facades\Activity;
use App\Models\Traits\HasAccessTokens;
use App\Models\Traits\HasRealtimeIdentifier;
use App\Notifications\SendPasswordReset as ResetPasswordNotification;
use App\Rules\Username;
use App\Traits\Helpers\AvailableLanguages;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\In;

/**
 * App\Models\User.
 *
 * @property int $id
 * @property string|null $external_id
 * @property string $uuid
 * @property string $username
 * @property string $email
 * @property string|null $name_first
 * @property string|null $name_last
 * @property string $password
 * @property string|null $remember_token
 * @property string $language
 * @property bool $root_admin
 * @property bool $use_totp
 * @property string|null $totp_secret
 * @property Carbon|null $totp_authenticated_at
 * @property bool $gravatar
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|ApiKey[] $apiKeys
 * @property int|null $api_keys_count
 * @property string $name
 * @property DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property int|null $notifications_count
 * @property \Illuminate\Database\Eloquent\Collection|RecoveryToken[] $recoveryTokens
 * @property int|null $recovery_tokens_count
 * @property \Illuminate\Database\Eloquent\Collection|Server[] $servers
 * @property int|null $servers_count
 * @property \Illuminate\Database\Eloquent\Collection|UserSSHKey[] $sshKeys
 * @property int|null $ssh_keys_count
 * @property \Illuminate\Database\Eloquent\Collection|ApiKey[] $tokens
 * @property int|null $tokens_count
 *
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereExternalId($value)
 * @method static Builder|User whereGravatar($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLanguage($value)
 * @method static Builder|User whereNameFirst($value)
 * @method static Builder|User whereNameLast($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRootAdmin($value)
 * @method static Builder|User whereTotpAuthenticatedAt($value)
 * @method static Builder|User whereTotpSecret($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUseTotp($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereUuid($value)
 *
 * @mixin \Eloquent
 */
#[Attributes\Identifiable('user')]
class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, HasAvatar, Identifiable
{
    use Authenticatable;
    use Authorizable;
    use AvailableLanguages;
    use CanResetPassword;

    /** @use HasAccessTokens<ApiKey> */
    use HasAccessTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRealtimeIdentifier;
    use Notifiable;

    public const USER_LEVEL_USER = 0;

    public const USER_LEVEL_ADMIN = 1;

    public function socialLogins(): HasMany
    {
        return $this->hasMany(SocialLogin::class);
    }

    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'user';

    /**
     * Level of servers to display when using access() on a user.
     */
    protected string $accessLevel = 'all';

    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * Use the ID as the route key name instead of the random UUID.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * A list of mass-assignable variables.
     */
    protected $fillable = [
        'external_id',
        'username',
        'email',
        'name_first',
        'name_last',
        'password',
        'language',
        'use_totp',
        'totp_secret',
        'totp_authenticated_at',
        'gravatar',
        'root_admin',
        'editor',
        'last_seen',
    ];

    /**
     * Cast values to correct type.
     */
    protected $casts = [
        'root_admin' => 'boolean',
        'use_totp' => 'boolean',
        'gravatar' => 'boolean',
        'totp_authenticated_at' => 'datetime',
        'editor' => 'string',
        'last_seen' => 'datetime',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     */
    protected $hidden = ['password', 'remember_token', 'totp_secret', 'totp_authenticated_at'];

    /**
     * Default values for specific fields in the database.
     */
    protected $attributes = [
        'external_id' => null,
        'root_admin' => false,
        'language' => 'en',
        'use_totp' => false,
        'totp_secret' => null,
    ];

    /**
     * Rules verifying that the data being stored matches the expectations of the database.
     */
    public static array $validationRules = [
        'uuid' => 'required|string|size:36|unique:users,uuid',
        'email' => 'required|email|between:1,191|unique:users,email',
        'external_id' => 'sometimes|nullable|string|max:191|unique:users,external_id',
        'username' => 'required|between:1,191|unique:users,username',
        'name_first' => 'required|string|between:1,191',
        'name_last' => 'required|string|between:1,191',
        'password' => 'sometimes|nullable|string',
        'root_admin' => 'boolean',
        'language' => 'string',
        'use_totp' => 'boolean',
        'totp_secret' => 'nullable|string',
    ];

    /**
     * Implement language verification by overriding Eloquence's gather
     * rules function.
     */
    public static function getRules(): array
    {
        $rules = parent::getRules();

        $rules['language'][] = new In([...array_keys((new self())->getAvailableLanguages()), 'geo']);
        $rules['username'][] = new Username();

        return $rules;
    }

    /**
     * Return the user model in a format that can be passed over to Vue templates.
     */
    public function toVueObject(): array
    {
        return Collection::make($this->toArray())->except(['id', 'external_id'])
            ->merge(['identifier' => $this->identifier])
            ->toArray();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token)
    {
        Activity::event('auth:reset-password')
            ->withRequestMetadata()
            ->subject($this)
            ->log('sending password reset email');

        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Store the username as a lowercase string.
     */
    public function setUsernameAttribute(string $value)
    {
        $this->attributes['username'] = mb_strtolower($value);
    }

    /**
     * Normalize the language value to be lowercase with no padded whitespace.
     */
    public function setLanguageAttribute(string $value): void
    {
        $this->attributes['language'] = trim(mb_strtolower($value));
    }

    /**
     * Return the language value with any padded whitespace removed.
     */
    public function getLanguageAttribute(?string $value): ?string
    {
        return is_null($value) ? null : trim($value);
    }

    /**
     * Return a concatenated result for the accounts full name.
     */
    public function getNameAttribute(): string
    {
        return trim($this->name_first.' '.$this->name_last);
    }

    /**
     * Returns all servers that a user owns.
     *
     * @return HasMany<Server, $this>
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class, 'owner_id');
    }

    /**
     * @return HasMany<ApiKey, $this>
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class)
            ->where('key_type', ApiKey::TYPE_ACCOUNT);
    }

    /**
     * @return HasMany<RecoveryToken, $this>
     */
    public function recoveryTokens(): HasMany
    {
        return $this->hasMany(RecoveryToken::class);
    }

    /**
     * @return HasMany<UserSSHKey, $this>
     */
    public function sshKeys(): HasMany
    {
        return $this->hasMany(UserSSHKey::class);
    }

    /**
     * Returns all the activity logs where this user is the subject — not to
     * be confused by activity logs where this user is the _actor_.
     *
     * @return MorphToMany<ActivityLog, $this>
     */
    public function activity(): MorphToMany
    {
        return $this->morphToMany(ActivityLog::class, 'subject', 'activity_log_subjects');
    }

    /**
     * Returns all the servers that a user can access by way of being the owner of the
     * server, or because they are assigned as a subuser for that server.
     *
     * @return Builder<Server>
     */
    public function accessibleServers(): Builder
    {
        return Server::query()
            ->where(function (Builder $builder) {
                $builder->where('servers.owner_id', $this->id)
                    ->orWhereExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('subusers')
                            ->whereColumn('subusers.server_id', 'servers.id')
                            ->where('subusers.user_id', $this->id);
                    });
            });
    }

    public function getGravatarUrlAttribute(): string
    {
        $email = strtolower(trim($this->email));
        $hash = md5($email);

        return "https://www.gravatar.com/avatar/{$hash}?s=200";
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->gravatar_url;
    }

    /**
     * @return HasMany<ServerCategory, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(ServerCategory::class);
    }
}
