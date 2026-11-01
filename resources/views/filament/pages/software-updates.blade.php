@php
    $busyStates = ['queued', 'downloading', 'validating', 'backing_up', 'installing', 'migrating', 'restarting'];
    $panelBusy = in_array($panel['status']['state'] ?? null, $busyStates, true);
    $panelStatus = ! $panel['automatic_supported'] ? $panel['automatic_error'] : (! $panel['latest_available'] ? 'check_failed' : ($panel['outdated'] ? 'update_available' : 'up_to_date'));
    $panelStatusColor = in_array($panelStatus, ['invalid_database', 'unsupported_installation'], true) ? 'danger' : ($panelStatus === 'update_available' ? 'warning' : ($panelStatus === 'up_to_date' ? 'success' : 'gray'));
@endphp
<x-filament-panels::page>
<div class="space-y-6"
    x-data="{
        panelUpdateRequested: {{ $panelBusy ? 'true' : 'false' }},
        panelUpdateObserved: {{ $panelBusy ? 'true' : 'false' }},
        panelUpdateBaseline: @js(collect($panel['status'] ?? [])->only(['state', 'version', 'updated_at'])->all()),
        panelUpdateMessage: @js($panelBusy ? ($panel['status']['message'] ?? trans('admin/updates.status.queued')) : trans('admin/updates.status.queued')),
        removeUpdateInterceptor: null,
        init() {
            this.$watch('$wire.panelUpdateInProgress', (busy) => {
                if (!busy || this.panelUpdateRequested) return

                this.panelUpdateRequested = true
                this.panelUpdateObserved = true
                this.stopPolling()
                this.startPanelUpdatePolling()
            })
            this.removeUpdateInterceptor = Livewire.interceptRequest(({ request, onError }) => {
                if (Array.from(request.messages).some((message) => Array.from(message.actions).some((action) => ['updateAgent', 'updateAllAgents'].includes(action.name)))) return
                onError(({ response, preventDefault }) => {
                    if (this.panelUpdateRequested && response.status >= 500) {
                        preventDefault()
                        this.panelWasUnavailable = true
                    }
                })
            })
        },
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

            let controller = new AbortController()
            this.panelUpdatePollController = controller
            let requestTimeout = setTimeout(() => controller.abort(), 5000)

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

                if (!response.ok) return

                const status = await response.json()
                this.panelWasUnavailable = false
                const changed = status.state !== this.panelUpdateBaseline?.state || status.version !== this.panelUpdateBaseline?.version || status.updated_at !== this.panelUpdateBaseline?.updated_at
                const terminal = ['complete', 'failed'].includes(status.state)
                if (this.panelUpdateRequestRejected && !this.panelUpdateObserved && Date.now() - this.panelUpdateStartedAt >= 10000 && (status.state === 'idle' || (terminal && !changed))) {
                    this.panelUpdateRequested = false
                    this.panelUpdateRequestRejected = false
                    this.stopPanelUpdatePolling()
                    this.startPolling()

                    return
                }

                const busy = ['queued', 'downloading', 'validating', 'backing_up', 'installing', 'migrating', 'restarting'].includes(status.state)
                if (busy) {
                    this.panelUpdateObserved = true
                    this.panelUpdateMessage = typeof status.message === 'string' ? status.message : ''
                }
                if (terminal && (this.panelUpdateObserved || changed)) {
                    clearTimeout(requestTimeout)
                    controller = new AbortController()
                    this.panelUpdatePollController = controller
                    requestTimeout = setTimeout(() => controller.abort(), 30000)
                    const page = await fetch(window.location.href, {
                        cache: 'no-store',
                        credentials: 'same-origin',
                        headers: { Accept: 'text/html' },
                        signal: controller.signal,
                    })
                    if (!page.ok || page.redirected) return
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
            this.removeUpdateInterceptor?.()
            this.stopPolling()
            this.stopPanelUpdatePolling()
        },
        updatePanel() {
            this.panelUpdateBaseline = this.$wire.panel.status
            this.panelUpdateObserved = false
            this.panelUpdateMessage = ''
            this.panelWasUnavailable = false
            this.panelUpdateStartedAt = Date.now()
            this.panelUpdateRequestRejected = false
            this.panelUpdateRequested = true
            this.stopPolling()
            this.startPanelUpdatePolling()
            this.$wire.updatePanel().then(() => {
                this.panelUpdateRequested = this.panelUpdateObserved || this.$wire.panelUpdateInProgress
                if (this.panelUpdateRequested) {
                    this.panelUpdateObserved = true
                    this.startPanelUpdatePolling()
                }

                if (!this.panelUpdateRequested) {
                    this.stopPanelUpdatePolling()
                    this.startPolling()
                }
            }).catch(() => {
                this.panelUpdateRequestRejected = true
                this.startPanelUpdatePolling()
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
                            @if ($panelBusy || ($panel['automatic_supported'] && $panel['outdated'] && $this::canUpdate()))
                                <div wire:ignore>
                                    <x-filament::button
                                        size="sm"
                                        x-on:click="if (!panelUpdateRequested) updatePanel()"
                                        x-bind:disabled="panelUpdateRequested"
                                        :disabled="$panelBusy"
                                        :loading-indicator="false"
                                        aria-live="polite"
                                    >
                                        <span class="software-update-button-content">
                                            <x-filament::icon x-show="!panelUpdateRequested" icon="heroicon-o-arrow-down-tray" class="h-5 w-5 shrink-0" />
                                            <x-filament::loading-indicator x-cloak x-show="panelUpdateRequested" class="h-5 w-5 shrink-0" />
                                            <span x-text="panelUpdateRequested ? (panelWasUnavailable ? @js(trans('admin/updates.reconnecting')) : (panelUpdateMessage || @js(trans('admin/updates.status.queued')))) : @js(trans('admin/updates.update'))">{{ $panelBusy ? ($panel['status']['message'] ?? trans('admin/updates.status.queued')) : trans('admin/updates.update') }}</span>
                                        </span>
                                    </x-filament::button>
                                </div>
                            @elseif ($panel['automatic_supported'] && $panel['latest_available'])
                                <x-filament::button size="sm" color="gray" icon="heroicon-o-check" disabled>{{ trans('admin/updates.up_to_date') }}</x-filament::button>
                            @else
                                <x-filament::button size="sm" color="gray" icon="heroicon-o-book-open" tag="a" href="https://docs.reviactyl.app/project/panel/updating-the-panel" target="_blank">{{ trans('admin/updates.documentation') }}</x-filament::button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section icon="heroicon-o-server-stack">
        <x-slot name="heading">{{ trans('admin/updates.agents') }}</x-slot>
        @if ($this::canUpdate() && collect($agents)->contains(fn ($agent) => $agent['reachable'] && $agent['outdated'] && $agent['installation_type'] === 'native' && ! in_array($agent['status']['state'] ?? null, $busyStates, true)))
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
                                    @elseif ($agent['reachable'] && $agent['latest_available'] && $agent['outdated'] && $agent['installation_type'] === 'native' && $this::canUpdate())
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
                                        <x-filament::button size="sm" color="gray" icon="heroicon-o-book-open" tag="a" href="https://docs.reviactyl.app/project/agent/updating-agent" target="_blank">{{ trans('admin/updates.documentation') }}</x-filament::button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</div>
</x-filament-panels::page>
