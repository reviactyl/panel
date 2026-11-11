import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { unload } from 'swr';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { useExtensions } from './useExtensions';

const mocks = vi.hoisted(() => ({ get: vi.fn(), user: undefined as { uuid: string } | undefined }));
vi.mock('@/api/http', () => ({ default: { get: mocks.get } }));
vi.mock('@/state/hooks', () => ({
    useStoreState: (selector: (state: unknown) => unknown) => selector({ user: { data: mocks.user } }),
}));
let root: Root;
let container: HTMLDivElement;
const Registry = () => {
    const { data } = useExtensions();
    return <p>{data ? 'loaded' : 'empty'}</p>;
};
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    unload({ revalidate: false });
    mocks.user = undefined;
    mocks.get.mockReset().mockResolvedValue({ data: { data: [] } });
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    unload({ revalidate: false });
});
it('avoids unauthorized guest requests and loads the private registry after in-app login', async () => {
    await act(async () => root.render(<Registry />));
    expect(mocks.get).not.toHaveBeenCalled();
    expect(container.textContent).toBe('empty');
    mocks.user = { uuid: 'signed-in' };
    await act(async () => root.render(<Registry />));
    expect(mocks.get).toHaveBeenCalledExactlyOnceWith('/api/client/extensions');
    expect(container.textContent).toBe('loaded');
});
