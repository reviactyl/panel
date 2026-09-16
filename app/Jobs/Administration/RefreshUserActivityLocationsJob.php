<?php

namespace App\Jobs\Administration;

use App\Jobs\Job;
use App\Models\User;
use App\Services\Helpers\GeoIPService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RefreshUserActivityLocationsJob extends Job implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;

    public const CACHE_KEY = 'metric:active_user_country_distribution_v3';

    private const MAX_GEOIP_LOOKUPS = 200;

    private const MAX_RESOLUTION_SECONDS = 15;

    public int $timeout = 30;

    public int $uniqueFor = 600;

    public bool $failOnTimeout = true;

    public function __construct()
    {
        $this->queue = 'standard';
    }

    /**
     * Cache active-user locations without making GeoIP requests during dashboard rendering.
     */
    public function handle(GeoIPService $geoIPService): void
    {
        $userMorphType = (new User())->getMorphClass();
        $activeSince = now()->subDays(30);
        $latestActivityIds = DB::table('activity_logs as activity')
            ->join('users', 'users.id', '=', 'activity.actor_id')
            ->where('activity.timestamp', '>=', $activeSince)
            ->where('activity.actor_type', $userMorphType)
            ->whereNotExists(function (Builder $query) use ($activeSince, $userMorphType): void {
                $query->selectRaw('1')
                    ->from('activity_logs as newer_activity')
                    ->whereColumn('newer_activity.actor_id', 'activity.actor_id')
                    ->where('newer_activity.actor_type', $userMorphType)
                    ->where('newer_activity.timestamp', '>=', $activeSince)
                    ->where(function (Builder $query): void {
                        $query->whereColumn('newer_activity.timestamp', '>', 'activity.timestamp')
                            ->orWhere(function (Builder $query): void {
                                $query->whereColumn('newer_activity.timestamp', 'activity.timestamp')
                                    ->whereColumn('newer_activity.id', '>', 'activity.id');
                            });
                    });
            })
            ->select('activity.id');
        $recentIpCounts = DB::table('activity_logs')
            ->whereIn('id', $latestActivityIds)
            ->select('ip')
            ->selectRaw('COUNT(*) as user_count')
            ->groupBy('ip')
            ->orderByDesc('user_count')
            ->cursor();

        $countryData = [];
        $lookupCount = 0;
        $resolutionDeadline = hrtime(true) + (self::MAX_RESOLUTION_SECONDS * 1_000_000_000);

        foreach ($recentIpCounts as $activity) {
            $canResolve = $lookupCount < self::MAX_GEOIP_LOOKUPS && hrtime(true) < $resolutionDeadline;
            $info = $canResolve ? $geoIPService->getCountryInfo((string) $activity->ip) : null;
            $lookupCount++;

            if (! $info || $info['country'] === 'Unknown') {
                $info = [
                    'country' => 'Unknown',
                    'code' => 'UNKNOWN',
                ];
            }

            $key = $info['code'];
            $countryData[$key] ??= [
                'country' => $info['country'],
                'code' => $info['code'],
                'count' => 0,
            ];
            $countryData[$key]['count'] += (int) $activity->user_count;
        }

        usort(
            $countryData,
            fn (array $a, array $b): int => ($b['count'] <=> $a['count']) ?: ($a['country'] <=> $b['country'])
        );

        Cache::put(self::CACHE_KEY, $countryData, 3600);
    }
}
