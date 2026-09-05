<x-filament-panels::page wire:poll.10s="refreshUpdates">
    <style>
        .software-update-summary {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        @media (min-width: 640px) {
            .software-update-summary {
                align-items: center;
                flex-direction: row;
                justify-content: space-between;
            }
        }
    </style>
    @php
        $busyStates = ['queued', 'downloading', 'validating', 'backing_up', 'installing', 'migrating', 'restarting'];
        $panelBusy = in_array($panel['status']['state'] ?? null, $busyStates, true);
    @endphp
    <p class="max-w-3xl text-sm text-gray-600 dark:text-gray-400">
        {{ trans('admin/updates.description') }}
    </p>

    <x-filament::section icon="heroicon-o-window">
        <x-slot name="heading">{{ trans('admin/updates.panel') }}</x-slot>

        <div class="software-update-summary">
            <dl style="display: grid; flex: 1 1 auto; grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr)); gap: 1rem 1.5rem; min-width: 0; width: 100%;">
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ trans('admin/updates.current') }}</dt>
                    <dd class="mt-1 font-mono text-sm font-semibold text-gray-950 dark:text-white">v{{ $panel['current'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ trans('admin/updates.latest') }}</dt>
                    <dd class="mt-1 font-mono text-sm font-semibold text-gray-950 dark:text-white">{{ $panel['latest_available'] ? 'v' . $panel['latest'] : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ trans('admin/updates.installation') }}</dt>
                    <dd class="mt-1">
                        <x-filament::badge :color="$panel['installation_type'] === 'native' ? 'success' : ($panel['installation_type'] === 'docker' ? 'info' : 'gray')">
                            {{ trans('admin/updates.' . $panel['installation_type']) }}
                        </x-filament::badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ trans('admin/updates.status_label') }}</dt>
                    <dd class="mt-1">
                        <x-filament::badge :color="! $panel['latest_available'] ? 'gray' : ($panel['outdated'] ? 'warning' : 'success')">
                            {{ trans(! $panel['latest_available'] ? 'admin/updates.check_failed' : ($panel['outdated'] ? 'admin/updates.update_available' : 'admin/updates.up_to_date')) }}
                        </x-filament::badge>
                    </dd>
                </div>
            </dl>

            <div class="flex shrink-0 items-center gap-2">
                @if ($panel['automatic_supported'] && $panel['outdated'])
                    <x-filament::button
                        icon="heroicon-o-arrow-down-tray"
                        wire:click="updatePanel"
                        wire:confirm="{{ trans('admin/updates.confirm_panel') }}"
                        wire:loading.attr="disabled"
                        :disabled="$panelBusy"
                    >
                        {{ trans('admin/updates.update') }}
                    </x-filament::button>
                @elseif (! $panel['automatic_supported'])
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-book-open"
                        tag="a"
                        href="https://reviactyl.app/docs/panel/updating-the-panel"
                        target="_blank"
                    >
                        {{ trans('admin/updates.documentation') }}
                    </x-filament::button>
                @endif
            </div>
        </div>

        @if ($panel['status'])
            <div class="mt-5 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700 ring-1 ring-gray-950/5 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10">
                {{ $panel['status']['message'] }}
            </div>
        @elseif (! $panel['automatic_supported'])
            <p class="mt-5 text-sm text-gray-600 dark:text-gray-400">
                {{ trans($panel['installation_type'] === 'docker' ? 'admin/updates.docker_help' : 'admin/updates.unsupported_panel_help') }}
            </p>
        @endif
    </x-filament::section>

    <x-filament::section icon="heroicon-o-server-stack">
        <x-slot name="heading">{{ trans('admin/updates.agents') }}</x-slot>
        @if (collect($agents)->contains(fn ($agent) => $agent['reachable'] && $agent['outdated'] && $agent['installation_type'] === 'native' && ! in_array($agent['status']['state'] ?? null, $busyStates, true)))
            <x-slot name="afterHeader">
                <x-filament::button
                    color="gray"
                    size="sm"
                    icon="heroicon-o-arrow-path"
                    wire:click="updateAllAgents"
                    wire:confirm="{{ trans('admin/updates.confirm_all') }}"
                    wire:loading.attr="disabled"
                >
                    {{ trans('admin/updates.update_all') }}
                </x-filament::button>
            </x-slot>
        @endif

        @if ($agents === [])
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ trans('admin/updates.empty') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-left text-sm" style="min-width: 760px;">
                    <thead class="border-b border-gray-200 text-xs font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3">{{ trans('admin/updates.component') }}</th>
                            <th class="px-3 py-3">{{ trans('admin/updates.current') }}</th>
                            <th class="px-3 py-3">{{ trans('admin/updates.latest') }}</th>
                            <th class="px-3 py-3">{{ trans('admin/updates.installation') }}</th>
                            <th class="px-3 py-3">{{ trans('admin/updates.status_label') }}</th>
                            <th class="px-3 py-3 text-right">{{ trans('admin/updates.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($agents as $agent)
                            <tr>
                                <td class="px-3 py-4 font-medium text-gray-950 dark:text-white">{{ $agent['name'] }}</td>
                                <td class="px-3 py-4 font-mono text-gray-700 dark:text-gray-300">{{ $agent['current'] === 'unavailable' ? '—' : 'v' . $agent['current'] }}</td>
                                <td class="px-3 py-4 font-mono text-gray-700 dark:text-gray-300">{{ $agent['latest_available'] ? 'v' . $agent['latest'] : '—' }}</td>
                                <td class="px-3 py-4">
                                    <x-filament::badge :color="$agent['installation_type'] === 'native' ? 'success' : ($agent['installation_type'] === 'docker' ? 'info' : 'gray')">
                                        {{ trans('admin/updates.' . $agent['installation_type']) }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-3 py-4">
                                    @if (! $agent['reachable'])
                                        <x-filament::badge color="danger">{{ trans('admin/updates.unreachable') }}</x-filament::badge>
                                    @elseif (! $agent['latest_available'])
                                        <x-filament::badge color="gray">{{ trans('admin/updates.check_failed') }}</x-filament::badge>
                                    @else
                                        <x-filament::badge :color="$agent['outdated'] ? 'warning' : 'success'">
                                            {{ trans($agent['outdated'] ? 'admin/updates.update_available' : 'admin/updates.up_to_date') }}
                                        </x-filament::badge>
                                    @endif
                                    @if ($agent['status'])
                                        <p class="mt-2 max-w-xs text-xs text-gray-500 dark:text-gray-400">{{ $agent['status']['message'] }}</p>
                                    @elseif ($agent['installation_type'] === 'unknown' && $agent['reachable'])
                                        <p class="mt-2 max-w-xs text-xs text-gray-500 dark:text-gray-400">{{ trans('admin/updates.unknown_help') }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-right">
                                    @if ($agent['outdated'] && $agent['installation_type'] === 'native')
                                        @php($agentBusy = in_array($agent['status']['state'] ?? null, $busyStates, true))
                                        <x-filament::button
                                            size="sm"
                                            icon="heroicon-o-arrow-down-tray"
                                            wire:click="updateAgent({{ $agent['id'] }})"
                                            wire:confirm="{{ trans('admin/updates.confirm_agent') }}"
                                            wire:loading.attr="disabled"
                                            :disabled="$agentBusy"
                                        >
                                            {{ trans('admin/updates.update') }}
                                        </x-filament::button>
                                    @elseif ($agent['outdated'] || $agent['installation_type'] !== 'native')
                                        <x-filament::button
                                            color="gray"
                                            size="sm"
                                            icon="heroicon-o-book-open"
                                            tag="a"
                                            href="https://reviactyl.app/docs/agent/updating-agent"
                                            target="_blank"
                                        >
                                            {{ trans('admin/updates.documentation') }}
                                        </x-filament::button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
