export interface PrefetchedResponse {
    status: number;
    body: string;
    headers: Record<string, string>;
}

interface PrefetchEntry {
    at?: number;
    maxAge?: number;
    promise: Promise<PrefetchedResponse>;
}

declare global {
    interface Window {
        __REVIACTYL_PREFETCH__?: Record<string, PrefetchEntry>;
        __REVIACTYL_PREFETCH_SERVER__?: { id: string; uuid: string };
    }
}

export const PREFETCH_MAX_AGE = 20_000;

let pendingPageData: Promise<void> | undefined;
const PAGE_DATA_WAIT = 4_000;

export const prefetchKey = (url: string): string | null => {
    try {
        const parsed = new URL(url, window.location.origin);
        if (parsed.origin !== window.location.origin) return null;
        parsed.searchParams.sort();

        return parsed.pathname + parsed.search;
    } catch {
        return null;
    }
};

export const takePrefetched = (url: string): Promise<PrefetchedResponse> | undefined => {
    const store = window.__REVIACTYL_PREFETCH__;
    if (!store) return undefined;
    let key = prefetchKey(url);
    if (!key) return undefined;
    const server = window.__REVIACTYL_PREFETCH_SERVER__;
    const byUuid = server && `/api/client/servers/${server.uuid}`;
    if (server && !store[key] && (key.startsWith(`${byUuid}/`) || key.startsWith(`${byUuid}?`))) {
        key = `/api/client/servers/${server.id}${key.slice(byUuid!.length)}`;
    }
    const entry = store[key];
    if (!entry) return undefined;
    delete store[key];

    return entry.at === undefined || Date.now() - entry.at <= (entry.maxAge ?? PREFETCH_MAX_AGE)
        ? entry.promise
        : undefined;
};

export const clearPrefetched = () => {
    delete window.__REVIACTYL_PREFETCH__;
    delete window.__REVIACTYL_PREFETCH_SERVER__;
    pendingPageData = undefined;
};

export const takePrefetchedCovering = (
    path: string,
    param: string,
    values: string[],
): Promise<PrefetchedResponse> | undefined => {
    const store = window.__REVIACTYL_PREFETCH__;
    if (!store) return undefined;
    for (const key of Object.keys(store)) {
        const url = new URL(key, window.location.origin);
        if (url.pathname !== path) continue;
        const available = new Set((url.searchParams.get(param) ?? '').split(','));
        if (!values.every((value) => available.has(value))) continue;
        const entry = store[key]!;
        delete store[key];
        if (entry.at === undefined || Date.now() - entry.at <= (entry.maxAge ?? PREFETCH_MAX_AGE)) return entry.promise;
    }

    return undefined;
};

export const takePrefetchedLocale = (url: string): Promise<PrefetchedResponse> | undefined => {
    const wanted = new URL(url, window.location.origin);
    if (wanted.pathname !== '/locales/locale.json') return undefined;
    const store = window.__REVIACTYL_PREFETCH__;
    if (!store) return undefined;
    const locale = wanted.searchParams.get('locale');
    const namespaces = (wanted.searchParams.get('namespace') ?? '').split(',');
    for (const key of Object.keys(store)) {
        const candidate = new URL(key, window.location.origin);
        if (candidate.pathname !== wanted.pathname || candidate.searchParams.get('locale') !== locale) continue;
        const available = new Set((candidate.searchParams.get('namespace') ?? '').split(','));
        if (!namespaces.every((namespace) => available.has(namespace))) continue;
        const entry = store[key]!;
        delete store[key];
        if (entry.at === undefined || Date.now() - entry.at <= (entry.maxAge ?? PREFETCH_MAX_AGE)) return entry.promise;
    }

    return undefined;
};

export const seedPrefetched = (responses: Record<string, string>) => {
    const store = (window.__REVIACTYL_PREFETCH__ ??= {});
    for (const [url, body] of Object.entries(responses)) {
        const key = prefetchKey(url);
        if (!key) continue;
        store[key] = {
            at: Date.now(),
            promise: Promise.resolve({ status: 200, body, headers: { 'content-type': 'application/json' } }),
        };
        const server = key.match(/^\/api\/client\/servers\/([^/?]+)\?include=/);
        if (server) {
            try {
                const uuid = JSON.parse(body)?.attributes?.uuid;
                if (uuid) window.__REVIACTYL_PREFETCH_SERVER__ = { id: server[1]!, uuid };
            } catch {
                // Not a server response; nothing to map.
            }
        }
    }
};

/**
 * Remembers a request for a page's data, so the page's own requests wait for it (briefly) and
 * reuse its answers instead of each asking the panel separately.
 */
export const trackPageData = (request: Promise<Record<string, string> | undefined>) => {
    const pending: Promise<void> = request
        .then((responses) => {
            if (responses) seedPrefetched(responses);
        })
        .catch(() => undefined)
        .finally(() => {
            if (pendingPageData === pending) pendingPageData = undefined;
        });
    pendingPageData = pending;
};

/** Resolves once page data being loaded has arrived, or after a few seconds at most. */
export const waitForPageData = (): Promise<void> => {
    if (!pendingPageData) return Promise.resolve();
    let timer: ReturnType<typeof setTimeout> | undefined;
    return Promise.race([
        pendingPageData,
        new Promise<void>((resolve) => {
            timer = setTimeout(resolve, PAGE_DATA_WAIT);
        }),
    ]).finally(() => clearTimeout(timer));
};
