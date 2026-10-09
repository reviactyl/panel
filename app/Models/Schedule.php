<?php

namespace App\Models;

use App\Contracts\Extensions\HashidsInterface;
use App\Helpers\Utilities;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Factories\ScheduleFactory;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property string $cron_day_of_week
 * @property string $cron_month
 * @property string $cron_day_of_month
 * @property string $cron_hour
 * @property string $cron_minute
 * @property bool $is_active
 * @property string|null $processing_token
 * @property bool $is_processing
 * @property bool $only_when_online
 * @property Carbon|null $last_run_at
 * @property Carbon|null $next_run_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $hashid
 * @property Server $server
 * @property Collection<int, Task> $tasks
 */
class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'server_schedule';

    /**
     * The table associated with the model.
     */
    protected $table = 'schedules';

    /**
     * Always return the tasks associated with this schedule.
     */
    protected $with = ['tasks'];

    /**
     * Mass assignable attributes on this model.
     */
    protected $fillable = [
        'server_id',
        'name',
        'cron_day_of_week',
        'cron_month',
        'cron_day_of_month',
        'cron_hour',
        'cron_minute',
        'is_active',
        'is_processing',
        'only_when_online',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'server_id' => 'integer',
        'is_active' => 'boolean',
        'is_processing' => 'boolean',
        'only_when_online' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    protected $attributes = [
        'name' => null,
        'cron_day_of_week' => '*',
        'cron_month' => '*',
        'cron_day_of_month' => '*',
        'cron_hour' => '*',
        'cron_minute' => '*',
        'is_active' => true,
        'is_processing' => false,
        'only_when_online' => false,
    ];

    public static array $validationRules = [
        'server_id' => 'required|exists:servers,id',
        'name' => 'required|string|max:191',
        'cron_day_of_week' => 'required|string',
        'cron_month' => 'required|string',
        'cron_day_of_month' => 'required|string',
        'cron_hour' => 'required|string',
        'cron_minute' => 'required|string',
        'is_active' => 'boolean',
        'is_processing' => 'boolean',
        'only_when_online' => 'boolean',
        'last_run_at' => 'nullable|date',
        'next_run_at' => 'nullable|date',
    ];

    public function getRouteKeyName(): string
    {
        return $this->getKeyName();
    }

    /**
     * Returns the schedule's execution crontab entry as a string.
     *
     * @throws \Exception
     */
    public function getNextRunDate(): CarbonImmutable
    {
        return CarbonImmutable::instance(Utilities::getScheduleNextRunDate(
            $this->cron_minute,
            $this->cron_hour,
            $this->cron_day_of_month,
            $this->cron_month,
            $this->cron_day_of_week,
            $this->server->getScheduleTimezone()
        ));
    }

    /**
     * Return a hashid encoded string to represent the ID of the schedule.
     */
    public function getHashidAttribute(): string
    {
        return Container::getInstance()->make(HashidsInterface::class)->encode($this->id);
    }

    /**
     * Return tasks belonging to a schedule.
     *
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Return the server model that a schedule belongs to.
     *
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
