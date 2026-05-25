@php
    use App\Services\Helpers\SoftwareVersionService;

    $node = $getRecord();
    $url = $node->getConnectionAddress() . '/api/system';
    $token = $node->getDecryptedKey();

    $latestAgentVersion = app(SoftwareVersionService::class)->getDaemon();
@endphp

<div
    x-data="{ status: 'loading', tooltip: '', hovered: false, tipX: 0, tipY: 0 }"
    x-init="
        $watch('tooltip', function() {
            if (!hovered) return;
            var tip = $refs.tip;
            var pr = $el.getBoundingClientRect();
            var tw = tip.offsetWidth;
            var th = tip.offsetHeight;
            if (!tw) return;
            var ideal = pr.left + pr.width / 2 - tw / 2;
            tipX = Math.max(8, Math.min(ideal, window.innerWidth - tw - 8));
            tipY = pr.top - th - 4;
        });

        function normalizeVersion(version) {
            return String(version || '')
                .trim()
                .replace(/^v/i, '')
                .split('-')[0];
        }

        function compareVersions(a, b) {
            a = normalizeVersion(a).split('.').map(Number);
            b = normalizeVersion(b).split('.').map(Number);

            const len = Math.max(a.length, b.length);

            for (let i = 0; i < len; i++) {
                const av = a[i] || 0;
                const bv = b[i] || 0;

                if (av > bv) return 1;
                if (av < bv) return -1;
            }

            return 0;
        }

        (async function check() {
            try {
                const r = await fetch($el.dataset.url, {
                    headers: { Authorization: 'Bearer ' + $el.dataset.token },
                    signal: AbortSignal.timeout(5000)
                });
                if (r.ok) {
                    const j = await r.json();

                    const rawVersion = j.version ?? '?';
                    const version = normalizeVersion(rawVersion);
                    const latestAgent = normalizeVersion($el.dataset.latestAgent);

                    tooltip = 'v' + rawVersion;

                    if (compareVersions(version, '26.0.0') < 0) {
                        tooltip = 'Wings support will be dropped in future releases. Click here to upgrade to Agent.';
                        status = 'wings';
                    } else if (
                        latestAgent !== 'error' &&
                        compareVersions(version, latestAgent) < 0
                    ) {
                        tooltip = 'Agent outdated. v' + latestAgent + ' is available. Click here to update Agent.';
                        status = 'outdated';
                    } else {
                        tooltip = 'v' + rawVersion;
                        status = 'up';
                    }
                } else {
                    const httpTip = ($el.dataset.httpTemplate ?? 'HTTP __STATUS__').replace('__STATUS__', String(r.status));
                    tooltip = httpTip + ' - ' + ($el.dataset.checkConsole ?? 'check browser console');
                    status = 'down';
                }
            } catch (e) {
                const err = e instanceof Error ? e.message : 'Unknown error';
                const errTip = ($el.dataset.errorTemplate ?? '__ERROR__').replace('__ERROR__', err);
                tooltip = errTip + ' - ' + ($el.dataset.checkConsole ?? 'check browser console');
                status = 'down';
            }
            setTimeout(check, 5000);
        })();
    "
    data-url="{{ $url }}"
    data-token="{{ $token }}"
    data-latest-agent="{{ $latestAgentVersion }}"
    data-http-template="{{ trans('admin/node.table.health_http_status', ['status' => '__STATUS__']) }}"
    data-error-template="__ERROR__"
    data-check-console="{{ trans('admin/node.table.health_check_console') }}"
    @mouseenter="
        hovered = true;
        var tip = $refs.tip;
        var pr = $el.getBoundingClientRect();
        var tw = tip.offsetWidth;
        var th = tip.offsetHeight;
        if (tw > 0) {
            var ideal = pr.left + pr.width / 2 - tw / 2;
            tipX = Math.max(8, Math.min(ideal, window.innerWidth - tw - 8));
            tipY = pr.top - th - 4;
        }
    "
    @mouseleave="hovered = false"
    style="width:50px;display:inline-flex;align-items:center;justify-content:center;min-height:20px"
>
    <span
        x-ref="tip"
        x-text="tooltip"
        :style="`position:fixed;top:${tipY}px;left:${tipX}px;background:#18181b;color:#e2e8f0;font-size:12px;padding:2px 8px;border-radius:4px;white-space:nowrap;pointer-events:none;z-index:9999;visibility:${hovered && tooltip !== '' ? 'visible' : 'hidden'};`"
    ></span>
    <span x-show="status === 'loading'">
        <x-tabler-heart-question style="color:yellow;" />
    </span>

    <span x-show="status === 'up'" x-cloak>
        <x-tabler-heart-check style="color:green;" />
    </span>

    <span onclick="window.open('https://reviactyl.app/docs/agent/migrating-from-wings', '_blank')" x-show="status === 'wings'" x-cloak>
        <x-tabler-alert-triangle style="color:orange;" />
    </span>

    <span onclick="window.open('https://reviactyl.app/docs/agent/updating-agent', '_blank')" x-show="status === 'outdated'" x-cloak>
        <x-tabler-heart-exclamation style="color:orange;" />
    </span>

    <span x-show="status === 'down'" x-cloak>
        <x-tabler-heart-broken style="color:red;" />
    </span>
</div>
