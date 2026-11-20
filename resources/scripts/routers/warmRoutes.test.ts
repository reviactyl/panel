import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import warmRoutes from './warmRoutes';

describe('warmRoutes', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        vi.stubGlobal('requestIdleCallback', undefined);
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.unstubAllGlobals();
    });

    it('loads each screen once, one at a time, after the page has loaded', async () => {
        let release!: () => void;
        const first = vi.fn(() => new Promise<void>((resolve) => (release = resolve)));
        const second = vi.fn(async () => undefined);

        warmRoutes([first, undefined, second, first]);
        expect(first).not.toHaveBeenCalled();

        await vi.advanceTimersByTimeAsync(2000);
        expect(first).toHaveBeenCalledTimes(1);
        expect(second).not.toHaveBeenCalled();

        release();
        await vi.advanceTimersByTimeAsync(500);
        expect(second).toHaveBeenCalledTimes(1);
        expect(first).toHaveBeenCalledTimes(1);
    });

    it('keeps going when a screen fails to load', async () => {
        const second = vi.fn(async () => undefined);

        warmRoutes([() => Promise.reject(new Error('offline')), second]);
        await vi.advanceTimersByTimeAsync(3000);

        expect(second).toHaveBeenCalledTimes(1);
    });

    it('stops when cleaned up', async () => {
        const loader = vi.fn(async () => undefined);

        warmRoutes([loader])();
        await vi.advanceTimersByTimeAsync(5000);

        expect(loader).not.toHaveBeenCalled();
    });

    it('does nothing when the browser asks to save data', async () => {
        vi.stubGlobal('navigator', { connection: { saveData: true } });
        const loader = vi.fn(async () => undefined);

        warmRoutes([loader]);
        await vi.advanceTimersByTimeAsync(5000);

        expect(loader).not.toHaveBeenCalled();
    });
});
