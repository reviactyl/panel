<?php

namespace App\Services\Http;

use App\Http\Controllers\Base\LocaleController;
use Illuminate\Http\Request;

class InitialApiResponses
{
    public function __construct(private InternalApiRequests $api, private LocaleController $locales) {}

    public function forPage(Request $request, string $path, int $page = 1, bool $websocket = false): array
    {
        $path = '/'.trim($path, '/');
        $responses = $this->translations($request, $path);
        if (! $request->user()) {
            return $responses;
        }

        $add = function (string $uri) use (&$responses, $request): ?array {
            $body = $this->api->get($request, $uri);
            if ($body === null) {
                return null;
            }
            $responses[$uri] = $body;

            return json_decode($body, true) ?: null;
        };

        if (preg_match('#^/server/([^/]+)#', $path, $matches)) {
            $id = rawurlencode($matches[1]);
            $server = $add("/api/client/servers/{$id}?include=allocations,variables,category");
            if ($uuid = $server['attributes']['uuid'] ?? null) {
                $add('/api/client/alerts?placement=server&server='.rawurlencode($uuid));
                if ($websocket) {
                    $add("/api/client/servers/{$uuid}/websocket");
                }
            }
        } elseif ($path === '/') {
            $folders = config('designify.serverOrganization') === 'folders';
            $add('/api/client?include=category&page='.max(1, $page).($folders ? '&filter[category_uuid]=null' : ''));
            $add('/api/client/account/categories');
            $add('/api/client/alerts?placement=dashboard');
            $add('/api/client/eggs');
        } elseif (! preg_match('#^/account(/|$)#', $path)) {
            return $responses;
        }

        $add('/api/client/extensions');
        $add('/api/client/notifications?unread=0&page=1');

        return $responses;
    }

    public function translations(Request $request, string $path): array
    {
        $map = json_decode((string) @file_get_contents(resource_path('scripts/i18nNamespaces.json')), true);
        if (! is_array($map) || preg_match('#^/(admin|api|status|docs)(/|$)#', $path)) {
            return [];
        }

        if (str_starts_with($path, '/auth')) {
            $namespaces = $map['auth'];
        } else {
            $server = str_starts_with($path, '/server/');
            $namespaces = [...$map['app'], ...($server ? $map['server'] : $map['dashboard'])];
            if ($server) {
                $screen = explode('/', $path)[3] ?? '';
                $namespaces = [...$namespaces, ...($map['serverScreens'][$screen ?: 'console'] ?? [])];
            }
        }
        $namespaces = array_values(array_unique([...$map['always'], ...$namespaces]));

        $user = $request->user();
        $language = $user && $user->language && $user->language !== 'geo' ? $user->language : config('app.locale', 'en');
        if (! is_string($language) || ! preg_match('/^[a-z]{2}$/', $language)) {
            return [];
        }
        $languages = array_values(array_unique([$language, 'en']));

        $uri = '/locales/locale.json?locale='.rawurlencode(implode('+', $languages)).'&namespace='.rawurlencode(implode(',', $namespaces));

        return [$uri => json_encode($this->locales->payload($languages, $namespaces), JSON_THROW_ON_ERROR)];
    }
}
