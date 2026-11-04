import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import SearchModal from './SearchModal';

const mocks = vi.hoisted(() => ({ getServers: vi.fn(), clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }));
vi.mock('@/api/getServers', () => ({ default: mocks.getServers }));
vi.mock('easy-peasy', () => ({
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({ user: { data: { rootAdmin: false, rolePermissions: [] } } }),
    useStoreActions: (selector: (actions: unknown) => unknown) => selector({ flashes: mocks }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({ visible, children }: { visible: boolean; children: ReactNode }) => (visible ? children : null),
}));
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
    let resolve!: (value: ReturnType<typeof response>) => void;
    const promise = new Promise<ReturnType<typeof response>>((res) => (resolve = res));
    return { promise, resolve };
};

let container: HTMLDivElement;
let root: Root;
const input = () => container.querySelector<HTMLInputElement>('input[name="term"]')!;
const loading = () => container.querySelector('[data-loading]')?.getAttribute('data-loading');
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
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
    await act(async () =>
        root.render(
            <MemoryRouter>
                <SearchModal visible onDismissed={() => {}} />
            </MemoryRouter>,
        ),
    );
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.useRealTimers();
});

describe('server search input', () => {
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

        expect(container.textContent).toContain('Current server');
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
        expect(container.textContent).not.toContain('Obsolete server');
        expect(loading()).toBe('true');
        await advance(500);
        expect(mocks.getServers).toHaveBeenLastCalledWith({ query: 'miXne', type: undefined });
        await act(async () => newer.resolve(response('Latest server')));
        expect(container.textContent).toContain('Latest server');
        expect(loading()).toBe('false');
        expect(input()).toBe(currentInput);
    });
});
