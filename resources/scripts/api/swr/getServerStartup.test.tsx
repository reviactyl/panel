import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { SWRConfig } from 'swr';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import http from '@/api/http';
import getServerStartup from './getServerStartup';

vi.mock('@/api/http', () => ({ default: { get: vi.fn() } }));
const seeded = { invocation: 'known command', variables: [], dockerImages: { image: 'image' } };
let root: Root;
let element: HTMLDivElement;
const Child = () => {
    getServerStartup('server');
    return null;
};
const Reader = () => {
    const { data, error, mutate } = getServerStartup('server', seeded, { errorRetryCount: 0 });
    return (
        <>
            <p>{data?.invocation}</p>
            <p>{error ? 'failed' : ''}</p>
            <button onClick={() => mutate()}>Retry</button>
            <Child />
        </>
    );
};
const mount = async () => {
    await act(async () =>
        root.render(
            <SWRConfig value={{ provider: () => new Map(), revalidateOnFocus: false }}>
                <Reader />
            </SWRConfig>,
        ),
    );
};
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    element = document.createElement('div');
    document.body.append(element);
    root = createRoot(element);
    vi.mocked(http.get).mockReset();
});
afterEach(async () => {
    await act(async () => root.unmount());
    element.remove();
});
describe('startup refresh', () => {
    it('renders known values immediately and shares one background request with subscribers', async () => {
        let finish!: (value: any) => void;
        vi.mocked(http.get).mockReturnValue(
            new Promise((resolve) => {
                finish = resolve;
            }),
        );
        await mount();
        expect(element.textContent).toContain('known command');
        expect(http.get).toHaveBeenCalledTimes(1);
        await act(async () =>
            finish({
                data: { data: [], meta: { startup_command: 'fresh command', docker_images: { other: 'other' } } },
            }),
        );
        expect(element.textContent).toContain('fresh command');
        expect(http.get).toHaveBeenCalledTimes(1);
    });
    it('retains seeded values on failure and permits a successful retry', async () => {
        vi.mocked(http.get).mockRejectedValueOnce(new Error('offline'));
        await mount();
        expect(element.textContent).toContain('known command');
        expect(element.textContent).toContain('failed');
        vi.mocked(http.get).mockResolvedValueOnce({
            data: { data: [], meta: { startup_command: 'retried', docker_images: {} } },
        });
        await act(async () => element.querySelector('button')!.click());
        expect(element.textContent).toContain('retried');
        expect(element.textContent).not.toContain('failed');
        expect(http.get).toHaveBeenCalledTimes(2);
    });
});
