@php
    $node = $getRecord();
    $url = $node->getConnectionAddress() . '/api/system';
    $token = $node->getDecryptedKey();
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
        (async function check() {
            try {
                const r = await fetch($el.dataset.url, {
                    headers: { Authorization: 'Bearer ' + $el.dataset.token },
                    signal: AbortSignal.timeout(5000)
                });
                if (r.ok) {
                    const j = await r.json();
                    tooltip = (j.version ?? '?');
                    status = 'up';
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
    data-http-template="{{ trans('admin/node.table.health_http_status', ['status' => '__STATUS__']) }}"
    data-error-template="{{ trans('admin/node.table.health_error', ['error' => '__ERROR__']) }}"
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

    <span x-show="status === 'down'" x-cloak>
        <x-tabler-heart-broken style="color:red;" />
    </span>
</div>
