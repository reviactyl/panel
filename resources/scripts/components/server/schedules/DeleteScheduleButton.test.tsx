import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Server } from '@/api/server/getServer';
import type { Schedule } from '@/api/server/schedules/getServerSchedules';
import { ServerContext } from '@/state/server';
import DeleteScheduleButton from './DeleteScheduleButton';

const mocks = vi.hoisted(() => ({ deleteSchedule: vi.fn(), clearFlashes: vi.fn(), addError: vi.fn() }));
vi.mock('@/api/server/schedules/deleteSchedule', () => ({ default: mocks.deleteSchedule }));
vi.mock('easy-peasy', async (importOriginal) => ({
    ...(await importOriginal<typeof import('easy-peasy')>()),
    useStoreActions: () => mocks,
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/dialog', () => ({
    Dialog: {
        Confirm: ({ open, onConfirmed, children }: { open: boolean; onConfirmed: () => void; children: ReactNode }) =>
            open ? <button onClick={onConfirmed}>{children}Confirm deletion</button> : null,
    },
}));

let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof ServerContext.useStore>;
const CaptureStore = () => {
    store = ServerContext.useStore();
    return null;
};
const records = [{ id: 1 }, { id: 2 }] as Schedule[];
const onDeleted = vi.fn();
const confirmDeletion = async () => {
    await act(async () => container.querySelector<HTMLButtonElement>('button')!.click());
    const confirm = Array.from(container.querySelectorAll('button')).find((button) =>
        button.textContent?.includes('Confirm deletion'),
    )!;
    await act(async () => confirm.click());
};

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.deleteSchedule.mockReset();
    onDeleted.mockReset();
    container = document.createElement('div');
    root = createRoot(container);
    await act(async () => {
        root.render(
            <ServerContext.Provider>
                <CaptureStore />
            </ServerContext.Provider>,
        );
    });
    act(() => {
        store.getActions().server.setServer({ id: 'server-id', uuid: 'server-uuid' } as Server);
        store.getActions().schedules.setSchedules(records);
    });
    await act(async () => {
        root.render(
            <ServerContext.Provider>
                <CaptureStore />
                <DeleteScheduleButton scheduleId={1} onDeleted={onDeleted} />
            </ServerContext.Provider>,
        );
    });
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.restoreAllMocks();
});

describe('schedule deletion', () => {
    it('waits for success and removes only the deleted schedule before returning to the list', async () => {
        let complete!: () => void;
        mocks.deleteSchedule.mockReturnValueOnce(new Promise<void>((resolve) => (complete = resolve)));
        onDeleted.mockImplementation(() => store.getState().schedules.data);
        await confirmDeletion();
        expect(mocks.deleteSchedule).toHaveBeenCalledWith('server-uuid', 1);
        expect(store.getState().schedules.data).toEqual(records);
        expect(onDeleted).not.toHaveBeenCalled();

        await act(async () => complete());

        expect(store.getState().schedules.data).toEqual([records[1]]);
        expect(onDeleted).toHaveBeenCalledOnce();
        expect(onDeleted).toHaveReturnedWith([records[1]]);
    });

    it('keeps the cached schedules and stays on the page when deletion fails', async () => {
        const error = new Error('Deletion failed');
        mocks.deleteSchedule.mockRejectedValueOnce(error);
        vi.spyOn(console, 'error').mockImplementation(() => {});

        await confirmDeletion();

        expect(store.getState().schedules.data).toEqual(records);
        expect(onDeleted).not.toHaveBeenCalled();
        expect(mocks.addError).toHaveBeenCalledWith({ key: 'schedules', message: 'Deletion failed' });
        expect(container.textContent).not.toContain('Confirm deletion');
    });
});
