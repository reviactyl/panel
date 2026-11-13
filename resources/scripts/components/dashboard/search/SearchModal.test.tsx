import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import SearchModal from './SearchModal';
import { createStore, StoreProvider } from 'easy-peasy';
import flashes from '@/state/flashes';

const mocks = vi.hoisted(() => ({ getServers: vi.fn() }));
vi.mock('@/api/getServers', () => ({ default: mocks.getServers }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/InputSpinner', () => ({
    default: ({ visible, children }: { visible: boolean; children: ReactNode }) => (
        <div data-loading={visible}>{children}</div>
    ),
}));

const response = (name: string) => ({
    items: [{ uuid: name, id: name, name, allocations: [], node: 'Node' }],
    pagination: { total: 1, count: 1, perPage: 50, currentPage: 1, totalPages: 1 },
});
const deferred = () => {
    let reject!: (error: Error) => void;
    let resolve!: (value: ReturnType<typeof response>) => void;
    const promise = new Promise<ReturnType<typeof response>>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
};

const makeStore = () => createStore({ flashes, user: { data: { rootAdmin: false, rolePermissions: [] } } });
let store: ReturnType<typeof makeStore>;
let container: HTMLDivElement;
let root: Root;
const dialog = () => document.querySelector('[role=dialog]')!;
const input = () => dialog().querySelector<HTMLInputElement>('input[name="term"]')!;
const loading = () => dialog().querySelector('[data-loading]')?.getAttribute('data-loading');
const edit = async (value: string, caret = value.length) => {
    const element = input();
    await act(async () => {
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(element, value);
        element.setSelectionRange(caret, caret);
        element.dispatchEvent(new Event('input', { bubbles: true }));
    });
};
const advance = async (milliseconds: number) => {
    await act(async () => vi.advanceTimersByTimeAsync(milliseconds));
};

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    vi.clearAllMocks();
    mocks.getServers.mockReset().mockResolvedValue(response('Current server'));
    store = makeStore();
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
    await act(async () =>
        root.render(
            <StoreProvider store={store}>
                <MemoryRouter>
                    <SearchModal visible onDismissed={() => {}} />
                </MemoryRouter>
            </StoreProvider>,
        ),
    );
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.useRealTimers();
    vi.restoreAllMocks();
});

describe('server search input', () => {
    it('opens a named dialog without a content heading', () => {
        expect(dialog().querySelector('h1, h2, h3, h4, h5, h6')).toBeNull();
        expect(document.getElementById(dialog().getAttribute('aria-labelledby')!)?.textContent).toBe('search.label');
        expect(document.activeElement).toBe(input());
    });
    it('retains its node, focus, and caret through middle edits, submitting, and results with real Formik', async () => {
        const original = input();
        await edit('mine');
        input().focus();
        input().setSelectionRange(2, 2);
        await edit('miXne', 3);

        expect(input().value).toBe('miXne');
        expect(input().selectionStart).toBe(3);
        expect(input().selectionEnd).toBe(3);
        expect(input()).toBe(original);
        expect(document.activeElement).toBe(original);
        expect(loading()).toBe('true');

        await advance(500);

        expect(dialog().textContent).toContain('Current server');
        expect(loading()).toBe('false');
        expect(input()).toBe(original);
        expect(document.activeElement).toBe(original);
        expect(input().selectionStart).toBe(3);
        expect(input().selectionEnd).toBe(3);
    });

    it('debounces the current query and ignores a previous response while the new query is pending', async () => {
        const older = deferred();
        const newer = deferred();
        mocks.getServers.mockReturnValueOnce(older.promise).mockReturnValueOnce(newer.promise);
        await edit('mi');
        await advance(500);
        expect(mocks.getServers).not.toHaveBeenCalled();
        expect(loading()).toBe('false');

        await edit('min');
        await advance(250);
        await edit('mine');
        await advance(499);
        expect(mocks.getServers).not.toHaveBeenCalled();
        await advance(1);
        expect(mocks.getServers).toHaveBeenCalledExactlyOnceWith({ query: 'mine', type: undefined });

        await edit('miXne', 3);
        const currentInput = input();
        await act(async () => older.resolve(response('Obsolete server')));
        expect(dialog().textContent).not.toContain('Obsolete server');
        expect(loading()).toBe('true');
        await advance(500);
        expect(mocks.getServers).toHaveBeenLastCalledWith({ query: 'miXne', type: undefined });
        await act(async () => newer.resolve(response('Latest server')));
        expect(dialog().textContent).toContain('Latest server');
        expect(loading()).toBe('false');
        expect(input()).toBe(currentInput);
    });
});

describe('server search failures', () => {
    it('renders the request error inside the modal and retries the unchanged query on submit', async () => {
        vi.spyOn(console, 'error').mockImplementation(() => {});
        mocks.getServers.mockRejectedValueOnce(new Error('Search unavailable'));
        await edit('mine');
        await advance(500);

        expect(dialog().querySelector('[role="alert"]')?.textContent).toContain('Search unavailable');
        expect(loading()).toBe('false');
        expect(input().value).toBe('mine');
        expect(dialog().querySelector('button[type="submit"]')).not.toBeNull();

        await act(async () => {
            dialog()
                .querySelector('form')!
                .dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        });
        expect(dialog().querySelector('[role="alert"]')).toBeNull();
        expect(loading()).toBe('true');
        await advance(500);

        expect(mocks.getServers).toHaveBeenCalledTimes(2);
        expect(mocks.getServers).toHaveBeenLastCalledWith({ query: 'mine', type: undefined });
        expect(dialog().textContent).toContain('Current server');
        expect(loading()).toBe('false');
    });

    it('keeps newer successful results visible when an older request fails', async () => {
        const older = deferred();
        mocks.getServers.mockReturnValueOnce(older.promise);
        await edit('older');
        await advance(500);
        await edit('newer');
        await advance(500);
        expect(dialog().textContent).toContain('Current server');

        await act(async () => older.reject(new Error('Obsolete failure')));

        expect(dialog().textContent).toContain('Current server');
        expect(dialog().querySelector('[role="alert"]')).toBeNull();
        expect(store.getState().flashes.items).toEqual([]);
        expect(loading()).toBe('false');
    });
});
