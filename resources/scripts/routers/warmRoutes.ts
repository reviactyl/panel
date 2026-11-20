type Loader = (() => Promise<unknown>) | undefined;

const whenIdle = (callback: () => void): (() => void) => {
    if (typeof window.requestIdleCallback === 'function') {
        const handle = window.requestIdleCallback(callback, { timeout: 4000 });
        return () => window.cancelIdleCallback(handle);
    }
    const handle = setTimeout(callback, 200);
    return () => clearTimeout(handle);
};

export default function warmRoutes(loaders: Loader[]): () => void {
    const connection = (navigator as Navigator & { connection?: { saveData?: boolean } }).connection;
    if (connection?.saveData) return () => undefined;

    const queue = [...new Set(loaders.filter((loader): loader is NonNullable<Loader> => !!loader))];
    let cancelled = false;
    let cancelPending: (() => void) | undefined;

    const next = () => {
        const loader = queue.shift();
        if (cancelled || !loader) return;
        cancelPending = whenIdle(() => {
            if (cancelled) return;
            void loader()
                .catch(() => undefined)
                .then(next);
        });
    };
    const start = () => {
        const handle = setTimeout(next, 1500);
        cancelPending = () => clearTimeout(handle);
    };

    if (document.readyState === 'complete') start();
    else window.addEventListener('load', start, { once: true });

    return () => {
        cancelled = true;
        cancelPending?.();
        window.removeEventListener('load', start);
    };
}
