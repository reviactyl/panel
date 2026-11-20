import type { createBrowserRouter } from 'react-router-dom';
import http from '@/api/http';
import { prefetchGeneration, trackPageData } from '@/api/prefetch';
import { getPreviewToken } from '@/lib/subuserPreviewStorage';
import { store } from '@/state';

type Router = Pick<ReturnType<typeof createBrowserRouter>, 'subscribe'>;

// Pages whose first requests the server can answer together (see InitialApiResponses).
const pageOf = (pathname: string): string | null => {
    const path = pathname.replace(/\/+$/, '') || '/';
    if (path === '/') return 'dashboard';
    const server = path.match(/^\/server\/([^/]+)/);

    return server ? `server:${server[1]}` : null;
};

let last: { key: string; at: number; request: Promise<Record<string, string> | undefined> } | undefined;

/**
 * Requests everything a dashboard or server page is about to ask for, in one request (see
 * InitialApiResponses). Repeated calls for the same page within a few seconds share it.
 */
export const requestPageData = (
    pathname: string,
    search = '',
): Promise<Record<string, string> | undefined> | undefined => {
    const page = pageOf(pathname);
    if (!page || getPreviewToken()) return undefined;
    const number = Number(new URLSearchParams(search).get('page') || '1');
    // A session reset (sign out, sign in) must never hand the next account the previous one's data.
    const key = `${prefetchGeneration()}:${page}:${number}`;
    if (last && last.key === key && Date.now() - last.at < 3_000) return last.request;

    const request: Promise<Record<string, string> | undefined> = http
        .get('/api/client/page-data', { params: { path: pathname, ...(number > 1 ? { page: number } : {}) } })
        .then(({ data }) => data?.data as Record<string, string> | undefined)
        .catch((error) => {
            // A failed or cancelled request must not be reused by the next navigation.
            if (last?.request === request) last = undefined;
            throw error;
        });
    last = { key, at: Date.now(), request };

    return request;
};

/**
 * When the app moves to the dashboard or into another server, asks for that page's data up
 * front. Router subscribers run before the new page renders, so its own requests find this one
 * in flight and reuse its answers.
 */
export default function watchPageData(router: Router): () => void {
    let current = pageOf(window.location.pathname);

    return router.subscribe((state) => {
        const next = pageOf(state.location.pathname);
        const entered = next !== null && next !== current;
        current = next;
        if (!entered || !store.getState().user.data) return;

        const request = requestPageData(state.location.pathname, state.location.search);
        if (request) trackPageData(request);
    });
}
