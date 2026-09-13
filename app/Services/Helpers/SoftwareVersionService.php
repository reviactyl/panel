<?php

namespace App\Services\Helpers;

use App\Exceptions\Service\Helper\CdnVersionFetchingException;
use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;

class SoftwareVersionService
{
    public const VERSION_CACHE_KEY = 'panel:versioning_data';

    private static array $result;

    /**
     * SoftwareVersionService constructor.
     */
    public function __construct(
        protected CacheRepository $cache,
        protected Client $client,
    ) {
        self::$result = $this->cacheVersionData();
    }

    /**
     * Get the latest Panel version from the CDN or the selected official release channel.
     */
    public function getPanel(?string $channel = null): string
    {
        return $channel !== null ? $this->releaseVersion('panel', $channel) : Arr::get(self::$result, 'panel') ?? 'error';
    }

    /**
     * Get the latest Agent version from the CDN or the selected official release channel.
     */
    public function getDaemon(?string $channel = null): string
    {
        return $channel !== null ? $this->releaseVersion('agent', $channel) : Arr::get(self::$result, 'agent') ?? 'error';
    }

    /**
     * Get the URL to the discord server.
     */
    public function getDiscord(): string
    {
        return Arr::get(self::$result, 'discord') ?? 'https://reviactyl.app/discord';
    }

    /**
     * Get the URL for donations.
     */
    public function getDonations(): string
    {
        return Arr::get(self::$result, 'donations') ?? 'https://github.com/sponsors/reviactyl';
    }

    /**
     * Determine if the current version of the panel is the latest.
     */
    public function isLatestPanel(?string $channel = null): bool
    {
        if (config('app.version') === 'canary') {
            return true;
        }

        return version_compare(config('app.version'), $this->getPanel($channel)) >= 0;
    }

    /**
     * Determine if a passed daemon version string is the latest.
     */
    public function isLatestDaemon(string $version, ?string $channel = null): bool
    {
        if ($version === 'develop') {
            return true;
        }

        return version_compare($version, $this->getDaemon($channel)) >= 0;
    }

    private function releaseVersion(string $component, string $channel): string
    {
        if (! in_array($channel, ['stable', 'beta'], true)) {
            throw new \InvalidArgumentException('Invalid software update channel.');
        }

        return $this->cache->remember("panel:releases:{$component}:{$channel}", 300, function () use ($component, $channel): string {
            try {
                $latest = 'error';
                for ($page = 1; $page <= 10; $page++) {
                    $response = $this->client->request('GET', "https://api.github.com/repos/reviactyl/{$component}/releases", [
                        'query' => ['per_page' => 100, 'page' => $page],
                        'headers' => ['Accept' => 'application/vnd.github+json', 'User-Agent' => 'Reviactyl-Panel-Updater'],
                        'connect_timeout' => 5,
                        'timeout' => 15,
                    ]);
                    $releases = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
                    if ($response->getStatusCode() !== 200 || ! is_array($releases) || ! array_is_list($releases)) {
                        return 'error';
                    }
                    foreach ($releases as $release) {
                        $tag = $release['tag_name'] ?? '';
                        if (($release['draft'] ?? false) || ! is_string($tag)
                            || ! preg_match('/^v([0-9]+\.[0-9]+\.[0-9]+(?:-(?:beta|rc)[0-9]*(?:\.[0-9]+)*)?)$/i', $tag, $matches)) {
                            continue;
                        }
                        $version = $matches[1];
                        if ($channel === 'stable' && (($release['prerelease'] ?? false) || str_contains($version, '-'))) {
                            continue;
                        }
                        if ($latest === 'error' || version_compare($version, $latest, '>')) {
                            $latest = $version;
                        }
                    }
                    if (count($releases) < 100) {
                        return $latest;
                    }
                }
            } catch (\Throwable) {
                return 'error';
            }

            return $latest;
        });
    }

    /**
     * Keeps the versioning cache up-to-date with the latest results from the CDN.
     */
    protected function cacheVersionData(): array
    {
        return $this->cache->remember(self::VERSION_CACHE_KEY, CarbonImmutable::now()->addMinutes(config('panel.cdn.cache_time', 60)), function () {
            try {
                $response = $this->client->request('GET', config('panel.cdn.url'));

                if ($response->getStatusCode() === 200) {
                    return json_decode($response->getBody(), true);
                }

                throw new CdnVersionFetchingException();
            } catch (\Exception) {
                return [];
            }
        });
    }
}
