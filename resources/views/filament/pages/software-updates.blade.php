@php
    $busyStates = ['queued', 'downloading', 'validating', 'backing_up', 'installing', 'migrating', 'restarting'];
    $panelBusy = in_array($panel['status']['state'] ?? null, $busyStates, true);
    $panelStatus = ! $panel['automatic_supported'] ? $panel['automatic_error'] : (! $panel['latest_available'] ? 'check_failed' : ($panel['outdated'] ? 'update_available' : 'up_to_date'));
    $panelStatusColor = in_array($panelStatus, ['invalid_database', 'unsupported_installation'], true) ? 'danger' : ($panelStatus === 'update_available' ? 'warning' : ($panelStatus === 'up_to_date' ? 'success' : 'gray'));
@endphp
<x-filament-panels::page
    x-data="{
        panelUpdateRequested: {{ $panelBusy ? 'true' : 'false' }},
        pollTimer: null,
        panelUpdatePollTimer: null,
        panelUpdatePollController: null,
        panelUpdatePollingActive: false,
        panelWasUnavailable: false,
        panelUpdateStartedAt: null,
        panelUpdateRequestRejected: false,
        startPolling() {
            if (this.pollTimer || this.panelUpdateRequested) return

            this.pollTimer = setInterval(() => {
                if (!this.panelUpdateRequested && !this.$wire.panelUpdateInProgress) {
                    this.$wire.refreshUpdates()
                }
            }, 10000)
        },
        stopPolling() {
            if (!this.pollTimer) return

            clearInterval(this.pollTimer)
            this.pollTimer = null
        },
        startPanelUpdatePolling() {
            if (this.panelUpdatePollingActive) return

            this.panelUpdatePollingActive = true
            this.panelUpdateStartedAt ??= Date.now()
            this.pollPanelUpdate()
        },
        async pollPanelUpdate() {
            if (!this.panelUpdatePollingActive) return

            if (Date.now() - this.panelUpdateStartedAt >= 900000) {
                this.panelUpdatePollingActive = false
                window.location.reload()
                return
            }

            const controller = new AbortController()
            this.panelUpdatePollController = controller
            const requestTimeout = setTimeout(() => controller.abort(), 5000)

            try {
                const url = new URL('/admin/software-updates/status', window.location.origin)
                url.searchParams.set('update-poll', Date.now().toString())
                const response = await fetch(url, {
                    cache: 'no-store',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                })

                if (response.status >= 500) {
                    this.panelWasUnavailable = true
                    return
                }

                if (!response.ok) {
                    if (this.panelWasUnavailable && response.status === 404) {
                        this.panelUpdatePollingActive = false
                        window.location.reload()
                    }

                    return
                }

                const status = await response.json()
                if (this.panelUpdateRequestRejected && status.state === 'idle') {
                    this.panelUpdateRequested = false
                    this.panelUpdateRequestRejected = false
                    this.stopPanelUpdatePolling()
                    this.startPolling()

                    return
                }

                if (['complete', 'failed'].includes(status.state) || this.panelWasUnavailable) {
                    this.panelUpdatePollingActive = false
                    window.location.reload()
                }
            } catch {
                if (this.panelUpdatePollingActive) this.panelWasUnavailable = true
            } finally {
                clearTimeout(requestTimeout)
                if (this.panelUpdatePollController === controller) this.panelUpdatePollController = null
                if (this.panelUpdatePollingActive) {
                    this.panelUpdatePollTimer = setTimeout(() => {
                        this.panelUpdatePollTimer = null
                        this.pollPanelUpdate()
                    }, 1000)
                }
            }
        },
        stopPanelUpdatePolling() {
            this.panelUpdatePollingActive = false
            clearTimeout(this.panelUpdatePollTimer)
            this.panelUpdatePollTimer = null
            this.panelUpdatePollController?.abort()
            this.panelUpdatePollController = null
        },
        destroy() {
            this.stopPolling()
            this.stopPanelUpdatePolling()
        },
        updatePanel() {
            this.panelUpdateRequested = true
            this.stopPolling()
            this.startPanelUpdatePolling()

            this.$wire.updatePanel().then(() => {
                this.panelUpdateRequested = this.$wire.panelUpdateInProgress

                if (!this.panelUpdateRequested) {
                    this.stopPanelUpdatePolling()
                    this.startPolling()
                }
            }).catch(() => {
                this.panelUpdateRequestRejected = true
            })
        },
    }"
    x-init="panelUpdateRequested ? startPanelUpdatePolling() : startPolling()"
>
    <style>
        .software-update-table th,
        .software-update-table td,
        .software-update-table td > div {
            text-align: center !important;
        }

        .software-update-button-content {
            align-items: center;
            display: inline-flex;
            gap: 0.5rem;
            justify-content: center;
        }

    </style>

    <p class="max-w-3xl text-sm text-gray-600 dark:text-gray-400">{{ trans('admin/updates.description') }}</p>
    <div class="max-w-sm space-y-2">
        <label for="update-channel" class="text-sm font-medium text-gray-950 dark:text-white">{{ trans('admin/updates.channel') }}</label>
        <x-filament::input.wrapper>
            <x-filament::input.select id="update-channel" wire:model.live="channel" aria-describedby="update-channel-help">
                <option value="stable">{{ trans('admin/updates.stable') }}</option>
                <option value="beta">{{ trans('admin/updates.beta') }}</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
        <p id="update-channel-help" class="text-sm text-gray-600 dark:text-gray-400">{{ trans('admin/updates.channel_help') }}</p>
    </div>

    <x-filament::section icon="heroicon-o-window">
        <x-slot name="heading">{{ trans('admin/updates.panel') }}</x-slot>
        <div class="overflow-x-auto">
            <table class="software-update-table w-full table-fixed text-sm" style="min-width: 760px; width: 100%; table-layout: fixed;">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 13%;">
                    <col style="width: 13%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 22%;">
                </colgroup>
                <thead class="border-b border-gray-200 text-xs font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.component') }}</th>
                        <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.current') }}</th>
                        <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.latest') }}</th>
                        <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.installation') }}</th>
                        <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.status_label') }}</th>
                        <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    <tr>
                        <td class="px-3 py-4 font-medium text-gray-950 dark:text-white" style="text-align: center;"><div class="truncate" style="text-align: center;" title="{{ trans('admin/updates.panel') }}">{{ trans('admin/updates.panel') }}</div></td>
                        <td class="px-3 py-4 font-mono text-gray-700 dark:text-gray-300" style="text-align: center;">v{{ $panel['current'] }}</td>
                        <td class="px-3 py-4 font-mono text-gray-700 dark:text-gray-300" style="text-align: center;">{{ $panel['latest_available'] ? 'v' . $panel['latest'] : '—' }}</td>
                        <td class="px-3 py-4" style="text-align: center;"><x-filament::badge :color="$panel['installation_type'] === 'native' ? 'success' : ($panel['installation_type'] === 'docker' ? 'info' : 'gray')">{{ trans('admin/updates.' . $panel['installation_type']) }}</x-filament::badge></td>
                        <td class="px-3 py-4" style="text-align: center;">
                            <x-filament::badge :color="$panelStatusColor">{{ trans('admin/updates.' . $panelStatus) }}</x-filament::badge>
                            @if (($panel['status']['state'] ?? null) === 'failed')<p class="mt-2 max-w-xs text-xs text-red-600 dark:text-red-400">{{ $panel['status']['message'] }}</p>@endif
                        </td>
                        <td class="px-3 py-4" style="text-align: center;">
                            @if ($panelBusy)
                                <x-filament::button size="sm" color="gray" disabled aria-live="polite">
                                    <span class="software-update-button-content">
                                        <x-filament::loading-indicator class="h-5 w-5" />
                                        <span>{{ trans('admin/updates.status.queued') }}</span>
                                    </span>
                                </x-filament::button>
                            @elseif ($panel['automatic_supported'] && $panel['outdated'])
                                <x-filament::button
                                    size="sm"
                                    x-on:click="updatePanel()"
                                    x-bind:disabled="panelUpdateRequested"
                                    aria-live="polite"
                                >
                                    <span class="software-update-button-content">
                                        <x-filament::icon x-show="!panelUpdateRequested" icon="heroicon-o-arrow-down-tray" class="h-5 w-5" />
                                        <x-filament::loading-indicator x-cloak x-show="panelUpdateRequested" class="h-5 w-5" />
                                        <span x-text="panelUpdateRequested ? @js(trans('admin/updates.status.queued')) : @js(trans('admin/updates.update'))"></span>
                                    </span>
                                </x-filament::button>
                            @elseif ($panel['automatic_supported'] && $panel['latest_available'])
                                <x-filament::button size="sm" color="gray" icon="heroicon-o-check" disabled>{{ trans('admin/updates.up_to_date') }}</x-filament::button>
                            @else
                                <x-filament::button size="sm" color="gray" icon="heroicon-o-book-open" tag="a" href="https://reviactyl.app/docs/panel/updating-the-panel" target="_blank">{{ trans('admin/updates.documentation') }}</x-filament::button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section icon="heroicon-o-server-stack">
        <x-slot name="heading">{{ trans('admin/updates.agents') }}</x-slot>
        @if (collect($agents)->contains(fn ($agent) => $agent['reachable'] && $agent['outdated'] && $agent['installation_type'] === 'native' && ! in_array($agent['status']['state'] ?? null, $busyStates, true)))
            <x-slot name="afterHeader">
                <x-filament::button color="gray" size="sm" wire:click="updateAllAgents" wire:target="updateAllAgents" wire:loading.attr="disabled" :loading-indicator="false">
                    <span wire:loading.remove wire:target="updateAllAgents" class="software-update-button-content">
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5" />
                        <span>{{ trans('admin/updates.update_all') }}</span>
                    </span>
                    <span wire:loading.flex wire:target="updateAllAgents" class="software-update-button-content">
                        <x-filament::loading-indicator class="h-5 w-5" />
                        <span>{{ trans('admin/updates.status.queued') }}</span>
                    </span>
                </x-filament::button>
            </x-slot>
        @endif
        @if ($agents === [])
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ trans('admin/updates.empty') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="software-update-table w-full table-fixed text-sm" style="min-width: 760px; width: 100%; table-layout: fixed;">
                    <colgroup>
                        <col style="width: 22%;">
                        <col style="width: 13%;">
                        <col style="width: 13%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                        <col style="width: 22%;">
                    </colgroup>
                    <thead class="border-b border-gray-200 text-xs font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.node') }}</th>
                            <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.current') }}</th>
                            <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.latest') }}</th>
                            <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.installation') }}</th>
                            <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.status_label') }}</th>
                            <th class="px-3 py-3" style="text-align: center;">{{ trans('admin/updates.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($agents as $agent)
                            @php
                                $agentBusy = in_array($agent['status']['state'] ?? null, $busyStates, true);
                                $agentStatus = ! $agent['reachable'] ? 'unreachable' : (! $agent['latest_available'] ? 'check_failed' : ($agent['outdated'] ? 'update_available' : 'up_to_date'));
                                $agentStatusColor = $agentStatus === 'unreachable' ? 'danger' : ($agentStatus === 'update_available' ? 'warning' : ($agentStatus === 'up_to_date' ? 'success' : 'gray'));
                            @endphp
                            <tr>
                                <td class="px-3 py-4 font-medium text-gray-950 dark:text-white" style="text-align: center;">
                                    <div class="truncate" style="text-align: center;" title="{{ $agent['name'] }} (Node #{{ $agent['id'] }})">{{ $agent['name'] }}</div>
                                    @if (mb_strlen($agent['name']) > 28)<div class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400" style="text-align: center;">#{{ $agent['id'] }}</div>@endif
                                </td>
                                <td class="px-3 py-4 font-mono text-gray-700 dark:text-gray-300" style="text-align: center;">{{ $agent['current'] === 'unavailable' ? '—' : 'v' . $agent['current'] }}</td>
                                <td class="px-3 py-4 font-mono text-gray-700 dark:text-gray-300" style="text-align: center;">{{ $agent['latest_available'] ? 'v' . $agent['latest'] : '—' }}</td>
                                <td class="px-3 py-4" style="text-align: center;"><x-filament::badge :color="$agent['installation_type'] === 'native' ? 'success' : ($agent['installation_type'] === 'docker' ? 'info' : 'gray')">{{ trans('admin/updates.' . $agent['installation_type']) }}</x-filament::badge></td>
                                <td class="px-3 py-4" style="text-align: center;">
                                    <x-filament::badge :color="$agentStatusColor">{{ trans('admin/updates.' . $agentStatus) }}</x-filament::badge>
                                    @if (($agent['status']['state'] ?? null) === 'failed')<p class="mt-2 max-w-xs text-xs text-red-600 dark:text-red-400">{{ $agent['status']['message'] }}</p>
                                    @elseif ($agent['installation_type'] === 'unknown' && $agent['reachable'])<p class="mt-2 max-w-xs text-xs text-gray-500 dark:text-gray-400">{{ trans('admin/updates.unknown_help') }}</p>@endif
                                </td>
                                <td class="px-3 py-4" style="text-align: center;">
                                    @if ($agentBusy)
                                        <x-filament::button size="sm" color="gray" disabled aria-live="polite">
                                            <span class="software-update-button-content">
                                                <x-filament::loading-indicator class="h-5 w-5" />
                                                <span>{{ trans('admin/updates.status.queued') }}</span>
                                            </span>
                                        </x-filament::button>
                                    @elseif ($agent['reachable'] && $agent['latest_available'] && $agent['outdated'] && $agent['installation_type'] === 'native')
                                        <x-filament::button
                                            size="sm"
                                            wire:click="updateAgent({{ $agent['id'] }})"
                                            wire:target="updateAgent({{ $agent['id'] }})"
                                            wire:loading.attr="disabled"
                                            :loading-indicator="false"
                                            aria-live="polite"
                                        >
                                            <span wire:loading.remove wire:target="updateAgent({{ $agent['id'] }})" class="software-update-button-content">
                                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-5 w-5" />
                                                <span>{{ trans('admin/updates.update') }}</span>
                                            </span>
                                            <span wire:loading.flex wire:target="updateAgent({{ $agent['id'] }})" class="software-update-button-content">
                                                <x-filament::loading-indicator class="h-5 w-5" />
                                                <span>{{ trans('admin/updates.status.queued') }}</span>
                                            </span>
                                        </x-filament::button>
                                    @elseif ($agent['reachable'] && $agent['latest_available'] && ! $agent['outdated'])
                                        <x-filament::button size="sm" color="gray" icon="heroicon-o-check" disabled>{{ trans('admin/updates.up_to_date') }}</x-filament::button>
                                    @else
                                        <x-filament::button size="sm" color="gray" icon="heroicon-o-book-open" tag="a" href="https://reviactyl.app/docs/agent/updating-agent" target="_blank">{{ trans('admin/updates.documentation') }}</x-filament::button>
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
