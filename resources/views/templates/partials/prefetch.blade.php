@if (!empty($initialApiResponses))
    {{-- Responses the server already produced while rendering this page; see InitialApiResponsesComposer. --}}
    <script type="application/json" id="reviactyl-initial-responses">{!! json_encode($initialApiResponses, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
<script>
    (function () {
        var w = window;
        try {
            if (!w.fetch || !w.URL || sessionStorage.getItem('reviactyl:subuser-preview-token')) return;
        } catch (e) {
            return;
        }

        var store = (w.__REVIACTYL_PREFETCH__ = {});
        var key = function (url) {
            var parsed = new URL(url, location.origin);
            parsed.searchParams.sort();
            return parsed.pathname + parsed.search;
        };
        var initial = {};
        try {
            var embedded = document.getElementById('reviactyl-initial-responses');
            if (embedded) initial = JSON.parse(embedded.textContent || '{}');
        } catch (e) {}
        var get = function (url) {
            if (Object.prototype.hasOwnProperty.call(initial, url)) {
                var ready = Promise.resolve({
                    status: 200,
                    body: initial[url],
                    headers: { 'content-type': 'application/json' },
                });
                // As fresh as the page; allow for slow connections that take a while to start the app.
                store[key(url)] = { promise: ready, at: Date.now(), maxAge: 120000 };
                return ready;
            }
            var promise = fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            }).then(function (response) {
                return response.text().then(function (body) {
                    return {
                        status: response.status,
                        body: body,
                        headers: { 'content-type': response.headers.get('content-type') || '' },
                    };
                });
            });
            var entry = (store[key(url)] = { promise: promise });
            promise.then(
                function () {
                    entry.at = Date.now();
                },
                function () {},
            );
            return promise;
        };

        // Register everything the page was sent with, so the app finds it under its own URLs.
        var embeddedToken = false;
        Object.keys(initial).forEach(function (url) {
            get(url);
            if (/\/websocket$/.test(url)) embeddedToken = true;
        });
        if (!w.PanelUser) return;

        var path = location.pathname.replace(/\/+$/, '') || '/';
        var server = path.match(/^\/server\/([^/]+)/);
        if (path !== '/' && !server && !/^\/account(\/|$)/.test(path)) return;

        // Each page's own content is requested first; the navbar's extras follow.
        var extras = function () {
            get('/api/client/extensions');
            get('/api/client/notifications?unread=0&page=1');
        };

        if (server) {
            var segments = path.split('/');
            var id = encodeURIComponent(server[1]);
            get('/api/client/servers/' + id + '?include=allocations,variables,category')
                .then(function (response) {
                    var uuid = response.status === 200 && (JSON.parse(response.body).attributes || {}).uuid;
                    if (!uuid) return;
                    w.__REVIACTYL_PREFETCH_SERVER__ = { id: id, uuid: uuid };
                    get('/api/client/alerts?placement=server&server=' + encodeURIComponent(uuid));
                })
                .catch(function () {});
            // The API accepts the identifier from the URL, so these start right away instead of
            // waiting for the server response. The app asks for them by full UUID and reuses
            // them once that response confirms which UUID this identifier belongs to.
            var directory = null;
            if (segments[3] === 'files' && segments.length === 4) {
                try {
                    directory = location.hash.length > 1 ? decodeURIComponent(location.hash.slice(1)) : '/';
                } catch (e) {
                    directory = '/';
                }
                directory = directory.replace(/(\/(\/*))|(^$)/g, '/');
                if (directory === '/.recycle-bin' || directory.indexOf('/.recycle-bin/') === 0) directory = null;
            }
            if (directory) get('/api/client/servers/' + id + '/files/list?directory=' + encodeURIComponent(directory));
            if (!embeddedToken) get('/api/client/servers/' + id + '/websocket');
            if (directory === '/') get('/api/client/servers/' + id + '/files/recycle-bin');
            extras();
            return;
        }
        if (path !== '/') {
            extras();
            return;
        }

        var user = w.PanelUser || {};
        var design = w.PanelConfiguration || {};
        var showAll = false;
        try {
            showAll = JSON.parse(localStorage.getItem(user.uuid + ':show_all_servers') || 'false') === true;
        } catch (e) {}
        var page = parseInt(new URLSearchParams(location.search).get('page') || '1', 10);

        // The admin "all servers" view uses different queries; leave those to the app.
        if (!showAll) {
            get(
                '/api/client?include=category&page=' + (page > 0 ? page : 1) +
                    (design.serverOrganization === 'folders' ? '&filter[category_uuid]=null' : ''),
            ).then(function (response) {
                if (response.status !== 200) return;
                var uuids = (JSON.parse(response.body).data || [])
                    .map(function (item) {
                        return item.attributes || {};
                    })
                    .filter(function (server) {
                        return server.uuid && server.status !== 'suspended' && !server.is_node_under_maintenance;
                    })
                    .map(function (server) {
                        return server.uuid;
                    });
                // Every server's usage in one request; the dashboard asks for the visible ones.
                if (uuids.length) get('/api/client/resources?servers=' + uuids.slice(0, 50).join(','));
            }).catch(function () {});
        }
        get('/api/client/account/categories');
        get('/api/client/alerts?placement=dashboard');
        extras();
        if (!showAll) get('/api/client/eggs');
    })();
</script>
