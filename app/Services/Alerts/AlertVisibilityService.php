<?php

namespace App\Services\Alerts;

use App\Models\Alert;
use App\Models\AlertInteraction;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AlertVisibilityService
{
    private const SERVER_FILTERS = ['nodes', 'locations', 'nests', 'eggs'];

    /**
     * @return Collection<int, Alert>
     */
    public function forUser(User $user, string $placement, ?Server $server = null): Collection
    {
        $alerts = $this->activeAlerts($placement);
        if ($alerts->isEmpty()) {
            return $alerts;
        }

        $interactions = AlertInteraction::query()
            ->where('user_id', $user->id)
            ->whereIn('alert_id', $alerts->modelKeys())
            ->get()
            ->keyBy('alert_id');

        return $alerts
            ->reject(fn (Alert $alert) => $alert->isHiddenBy($interactions->get($alert->id)))
            ->filter(fn (Alert $alert) => $this->matchesUser($alert, $user))
            ->filter(fn (Alert $alert) => $this->matchesServers($alert, $user, $placement, $server))
            ->values();
    }

    /**
     * @return Collection<int, Alert>
     */
    public function forGuests(): Collection
    {
        return $this->activeAlerts(Alert::PLACEMENT_AUTH)
            ->reject(fn (Alert $alert) => $alert->hasAudienceRules())
            ->values();
    }

    public function isAvailableTo(Alert $alert, User $user): bool
    {
        return $alert->status() === Alert::STATUS_ACTIVE && $this->matchesUser($alert, $user);
    }

    public function dismiss(Alert $alert, User $user): void
    {
        $this->record($alert, $user, 'dismissed_at');
    }

    public function click(Alert $alert, User $user): void
    {
        $this->record($alert, $user, 'clicked_at');
    }

    public function resetDismissals(Alert $alert): void
    {
        $alert->interactions()->whereNotNull('dismissed_at')->update(['dismissed_at' => null]);

        $alert->forceFill(['dismissals_reset_at' => Carbon::now()])->save();
    }

    private function record(Alert $alert, User $user, string $column): void
    {
        $now = Carbon::now();

        AlertInteraction::query()->upsert(
            [[
                'alert_id' => $alert->id,
                'user_id' => $user->id,
                $column => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['alert_id', 'user_id'],
            [$column, 'updated_at'],
        );
    }

    /**
     * @return Collection<int, Alert>
     */
    private function activeAlerts(string $placement): Collection
    {
        return Alert::query()
            ->active()
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Alert $alert) => $alert->hasPlacement($placement))
            ->values();
    }

    private function matchesUser(Alert $alert, User $user): bool
    {
        if ($alert->audience === 'admins' && ! $user->root_admin) {
            return false;
        }

        if ($alert->audience === 'users' && $user->root_admin) {
            return false;
        }

        $users = array_map('intval', $alert->targetList('users'));
        if (! empty($users) && ! in_array($user->id, $users, true)) {
            return false;
        }

        $languages = $alert->targetList('languages');
        if (! empty($languages) && ! in_array($user->language, $languages, true)) {
            return false;
        }

        $twoFactor = $alert->targetValue('two_factor', 'any');
        if ($twoFactor === 'enabled' && ! $user->use_totp) {
            return false;
        }

        if ($twoFactor === 'disabled' && $user->use_totp) {
            return false;
        }

        return $this->matchesAccountAge($alert, $user);
    }

    private function matchesAccountAge(Alert $alert, User $user): bool
    {
        $minimum = $alert->targetValue('account_age_min');
        $maximum = $alert->targetValue('account_age_max');

        if ($minimum === null && $maximum === null) {
            return true;
        }

        if ($user->created_at === null) {
            return false;
        }

        $age = (int) $user->created_at->diffInDays(Carbon::now());

        return ($minimum === null || $age >= (int) $minimum) && ($maximum === null || $age <= (int) $maximum);
    }

    private function matchesServers(Alert $alert, User $user, string $placement, ?Server $server): bool
    {
        $relation = $alert->targetValue('servers', 'any');

        if ($relation === 'none') {
            return ! $user->accessibleServers()->exists();
        }

        $filters = [];
        foreach (self::SERVER_FILTERS as $filter) {
            $filters[$filter] = array_map('intval', $alert->targetList($filter));
        }

        if ($relation === 'any' && empty(array_filter($filters))) {
            return true;
        }

        $query = match ($relation) {
            'owner' => Server::query()->where('servers.owner_id', $user->id),
            'subuser' => Server::query()->whereHas('subusers', fn (Builder $builder) => $builder->where('user_id', $user->id)),
            default => $user->accessibleServers(),
        };

        if ($placement === Alert::PLACEMENT_SERVER && $server !== null) {
            $query = in_array($relation, ['owner', 'subuser'], true) ? $query : Server::query();
            $query->where('servers.id', $server->id);
        }

        if (! empty($filters['nodes'])) {
            $query->whereIn('servers.node_id', $filters['nodes']);
        }

        if (! empty($filters['locations'])) {
            $query->whereIn('servers.node_id', Node::query()->select('id')->whereIn('location_id', $filters['locations']));
        }

        if (! empty($filters['nests'])) {
            $query->whereIn('servers.nest_id', $filters['nests']);
        }

        if (! empty($filters['eggs'])) {
            $query->whereIn('servers.egg_id', $filters['eggs']);
        }

        return $query->exists();
    }
}
