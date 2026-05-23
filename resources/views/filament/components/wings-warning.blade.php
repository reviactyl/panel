@php
    use App\Services\Helpers\SoftwareVersionService;

    $node = $getRecord();
    $url = $node->getConnectionAddress() . '/api/system';
    $token = $node->getDecryptedKey();

    $latestAgentVersion = app(SoftwareVersionService::class)->getDaemon();
@endphp

<div
    x-data="{ status: 'loading', tooltip: '', hovered: false, tipX: 0, tipY: 0 }"
    x-effect="
        document.querySelectorAll('div[wire\\:partial=\'schema-component::form.health\']')
            .forEach(el => el.style.display = status === 'up' ? 'none' : '')
    "
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
>
    <div x-show="status === 'outdated'" x-cloak x-transition style="margin-top:0.5rem;">
        <div style="display:flex;gap:0.75rem;border-radius:0.75rem;padding:1rem;border:1px solid var(--warning-800);">
            <div style="flex-shrink:0;margin-top:0.125rem;">
                <x-tabler-heart-exclamation style="width:1.25rem;height:1.25rem;color:var(--warning-500);" />
            </div>
            <div style="display:flex;flex:1;flex-direction:column;gap:0.25rem;">
                <p style="font-size:0.875rem;font-weight:600;color:var(--warning-600);">
                    You're running an outdated version of the Agent.
                </p>
                <p
                    style="font-size:0.875rem;color:var(--warning-50);"
                >
                    Please update to the latest version to ensure continued support and access to new features. An outdated agent may have connectivity issues or lack important security updates.
                </p>
                <div style="margin-top:0.5rem;">
                    <a
                        href="https://reviactyl.app/docs/agent/updating-agent"
                        target="_blank"
                        rel="noopener noreferrer"
                        style="font-size:0.875rem;font-weight:500;color:var(--warning-500);"
                    >
                        View Update Guide &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div x-show="status === 'wings'" x-cloak x-transition style="margin-top:0.5rem;">
        <div style="display:flex;gap:0.75rem;border-radius:0.75rem;padding:1rem;border:1px solid var(--warning-800);">
            <div style="flex-shrink:0;margin-top:0.125rem;">
                <x-tabler-alert-triangle style="width:1.25rem;height:1.25rem;color:var(--warning-500);" />
            </div>
            <div style="display:flex;flex:1;flex-direction:column;gap:0.25rem;">
                <p style="font-size:0.875rem;font-weight:600;color:var(--warning-600);">
                    You're running Wings.
                </p>
                <p
                    style="font-size:0.875rem;color:var(--warning-50);"
                >
                    Most of the features are not compatible with wings. Please upgrade to the latest Reviactyl Agent to ensure continued support and access to new features.
                </p>
                <div style="margin-top:0.5rem;">
                    <a
                        href="https://reviactyl.app/docs/agent/migrating-from-wings"
                        target="_blank"
                        rel="noopener noreferrer"
                        style="font-size:0.875rem;font-weight:500;color:var(--warning-500);"
                    >
                        View Migration Guide &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div x-show="status === 'down'" x-cloak x-transition style="margin-top:0.5rem;">
        <div style="display:flex;gap:0.75rem;border-radius:0.75rem;padding:1rem;border:1px solid var(--warning-800);">
            <div style="flex-shrink:0;margin-top:0.125rem;">
                <x-tabler-heart-broken style="width:1.25rem;height:1.25rem;color:var(--danger-500);" />
            </div>
            <div style="display:flex;flex:1;flex-direction:column;gap:0.25rem;">
                <p style="font-size:0.875rem;font-weight:600;color:var(--danger-600);">
                    Agent is unreachable.
                </p>
                <p
                    style="font-size:0.875rem;color:var(--danger-50);"
                >
                    Your panel is unable to communicate with the Reviactyl Agent on this node. This could be due to a network issue, the agent being down, or a misconfiguration. Please investigate the issue to restore connectivity.
                </p>
                <div style="margin-top:0.5rem;">
                    <a
                        href="https://reviactyl.app/discord"
                        target="_blank"
                        rel="noopener noreferrer"
                        style="font-size:0.875rem;font-weight:500;color:var(--danger-500);"
                    >
                        Ask for Help &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
