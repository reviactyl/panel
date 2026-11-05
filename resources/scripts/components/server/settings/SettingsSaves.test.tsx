import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ServerContext } from '@/state/server';
import type { Server } from '@/api/server/getServer';
import RenameServerBox from './RenameServerBox';
import TimezoneBox from './TimezoneBox';

const mocks = vi.hoisted(() => ({
    rename: vi.fn(),
    timezone: vi.fn(),
    addError: vi.fn(),
    addFlash: vi.fn(),
    clearFlashes: vi.fn(),
}));
vi.mock('@/api/server/renameServer', () => ({ default: mocks.rename }));
vi.mock('@/api/server/setServerTimezone', () => ({ default: mocks.timezone }));
vi.mock('easy-peasy', async (importOriginal) => ({
    ...(await importOriginal<typeof import('easy-peasy')>()),
    useStoreActions: (selector: (state: unknown) => unknown) => selector({ flashes: mocks }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/TitledGreyBox', () => ({
    default: ({ children }: { children: ReactNode }) => <section>{children}</section>,
}));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({
    default: ({ visible }: { visible: boolean }) => <span data-loading={visible} />,
}));
vi.mock('@/lib/timezones', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@/lib/timezones')>()),
    getTimezones: () => ['UTC', 'America/New_York'],
}));
vi.mock('@headlessui/react', () => ({
    Combobox: ({ onChange, children }: { onChange: (option: { value: string }) => void; children: ReactNode }) => (
        <div>
            <select aria-label='Timezone' onChange={(event) => onChange({ value: event.target.value })}>
                <option value='UTC'>UTC</option>
                <option value='America/New_York'>America/New_York</option>
            </select>
            {children}
        </div>
    ),
    ComboboxButton: () => null,
    ComboboxInput: () => null,
    ComboboxOption: () => null,
    ComboboxOptions: () => null,
}));

let root: Root;
let container: HTMLDivElement;
let store: ReturnType<typeof ServerContext.useStore>;
const initial = {
    uuid: 'server-uuid',
    name: 'Old name',
    description: 'Old description',
    timezone: 'UTC',
    defaultTimezone: 'UTC',
    node: 'Original node',
} as Server;
const Capture = () => {
    store = ServerContext.useStore();
    const server = ServerContext.useStoreState((state) => state.server.data);
    return <output>{JSON.stringify(server)}</output>;
};
const deferred = () => {
    let resolve!: () => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<void>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
};
const edit = async (selector: string, value: string) => {
    await act(async () => {
        const input = container.querySelector<HTMLInputElement | HTMLTextAreaElement>(selector)!;
        const prototype =
            input instanceof HTMLTextAreaElement ? HTMLTextAreaElement.prototype : HTMLInputElement.prototype;
        Object.getOwnPropertyDescriptor(prototype, 'value')!.set!.call(input, value);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
};
const rename = async () => {
    await edit('input[name="name"]', 'New name');
    await edit('textarea', 'New description');
    await act(async () => {
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });
    expect(mocks.rename).toHaveBeenCalledExactlyOnceWith('server-uuid', 'New name', 'New description');
};
const timezone = async () => {
    await act(async () => {
        const select = container.querySelector('select')!;
        select.value = 'America/New_York';
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });
    await act(async () => {
        Array.from(container.querySelectorAll('button'))
            .find((button) => button.textContent === 'timezone.button')!
            .click();
    });
    expect(mocks.timezone).toHaveBeenCalledExactlyOnceWith('server-uuid', 'America/New_York');
};
const expectState = (expected: Server) => {
    expect(store.getState().server.data).toEqual(expected);
    expect(JSON.parse(container.querySelector('output')!.textContent!)).toEqual(expected);
};

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.rename.mockReset().mockResolvedValue(undefined);
    mocks.timezone.mockReset().mockResolvedValue(undefined);
    container = document.createElement('div');
    root = createRoot(container);
    await act(async () => {
        root.render(
            <ServerContext.Provider>
                <Capture />
            </ServerContext.Provider>,
        );
    });
    await act(async () => {
        store.getActions().server.setServer(initial);
    });
    await act(async () => {
        root.render(
            <ServerContext.Provider>
                <Capture />
                <RenameServerBox />
                <TimezoneBox />
            </ServerContext.Provider>,
        );
    });
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.restoreAllMocks();
});

describe('independent server settings saves', () => {
    it.each(['rename', 'timezone'])('preserves both persisted changes when %s finishes first', async (first) => {
        const requests = { rename: deferred(), timezone: deferred() };
        let persisted = { ...initial };
        mocks.rename.mockImplementationOnce(() =>
            requests.rename.promise.then(() => {
                persisted = { ...persisted, name: 'New name', description: 'New description' };
            }),
        );
        mocks.timezone.mockImplementationOnce(() =>
            requests.timezone.promise.then(() => {
                persisted = { ...persisted, timezone: 'America/New_York' };
            }),
        );
        await rename();
        await timezone();
        await act(async () => requests[first as keyof typeof requests].resolve());
        expectState(persisted);
        await act(async () => requests[first === 'rename' ? 'timezone' : 'rename'].resolve());
        expectState(persisted);
        expect(persisted).toEqual({
            ...initial,
            name: 'New name',
            description: 'New description',
            timezone: 'America/New_York',
        });
        expect(mocks.addError).not.toHaveBeenCalled();
        expect(mocks.addFlash).toHaveBeenCalledWith({ key: 'settings', type: 'success', message: 'timezone.saved' });
        expect(
            Array.from(container.querySelectorAll('[data-loading]')).every(
                (element) => element.getAttribute('data-loading') === 'false',
            ),
        ).toBe(true);
    });

    it('saves a rename alone', async () => {
        await rename();
        expectState({ ...initial, name: 'New name', description: 'New description' });
        expect(mocks.addError).not.toHaveBeenCalled();
    });
    it('saves a timezone alone and invalidates cached schedules', async () => {
        await act(async () => {
            store.getActions().schedules.setSchedules([{ id: 1 } as never]);
        });
        await timezone();
        expectState({ ...initial, timezone: 'America/New_York' });
        expect(store.getState().schedules.data).toEqual([]);
        expect(mocks.addFlash).toHaveBeenCalledWith({ key: 'settings', type: 'success', message: 'timezone.saved' });
    });
    it.each(['rename', 'timezone'])('keeps state and reports an individual %s failure', async (kind) => {
        vi.spyOn(console, 'error').mockImplementation(() => undefined);
        mocks[kind as 'rename' | 'timezone'].mockRejectedValueOnce(new Error('Save failed'));
        await (kind === 'rename' ? rename() : timezone());
        expectState(initial);
        if (kind === 'rename') expect(mocks.addError).toHaveBeenCalledWith({ key: 'settings', message: 'Save failed' });
        else expect(mocks.addFlash).toHaveBeenCalledWith({ key: 'settings', type: 'error', message: 'Save failed' });
        expect(
            Array.from(container.querySelectorAll('[data-loading]')).every(
                (element) => element.getAttribute('data-loading') === 'false',
            ),
        ).toBe(true);
    });
});
