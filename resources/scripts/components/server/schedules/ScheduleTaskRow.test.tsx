import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Server } from '@/api/server/getServer';
import type { Schedule, Task } from '@/api/server/schedules/getServerSchedules';
import { ServerContext } from '@/state/server';
import ScheduleTaskRow from './ScheduleTaskRow';

const mocks = vi.hoisted(() => ({ deleteTask: vi.fn(), clearFlashes: vi.fn(), addError: vi.fn() }));
vi.mock('@/api/server/schedules/deleteScheduleTask', () => ({ default: mocks.deleteTask }));
vi.mock('@/plugins/useFlash', () => ({ default: () => mocks }));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [true] }));
vi.mock('react-i18next', () => ({
    useTranslation: () => ({
        t: (key: string, options?: { time: number }) =>
            key === 'time-offset-later' ? `${options?.time} seconds later` : key,
    }),
}));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('./TaskDetailsModal', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/ConfirmationModal', () => ({
    default: ({ visible, onConfirmed }: { visible: boolean; onConfirmed: () => void }) =>
        visible ? <button onClick={onConfirmed}>Confirm deletion</button> : null,
}));

const task = (id: number, timeOffset = 0): Task => ({
    id,
    sequenceId: id,
    action: 'command',
    payload: `say task ${id}`,
    timeOffset,
    isQueued: false,
    continueOnFailure: false,
    createdAt: new Date('2026-11-01T00:00:00Z'),
    updatedAt: new Date('2026-11-01T00:00:00Z'),
});
const schedule = (tasks: Task[]): Schedule => ({
    id: 1,
    name: 'Daily commands',
    cron: { minute: '0', hour: '12', dayOfMonth: '*', month: '*', dayOfWeek: '*' },
    isActive: false,
    isProcessing: false,
    onlyWhenOnline: false,
    lastRunAt: null,
    nextRunAt: null,
    createdAt: new Date('2026-11-01T00:00:00Z'),
    updatedAt: new Date('2026-11-01T00:00:00Z'),
    tasks,
});

let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof ServerContext.useStore>;
const Rows = () => {
    store = ServerContext.useStore();
    const current = ServerContext.useStoreState((state) => state.schedules.data[0]);
    return current?.tasks.map((task) => <ScheduleTaskRow key={task.id} schedule={current} task={task} />);
};
const render = async (tasks: Task[]) => {
    act(() => {
        store.getActions().server.setServer({ id: 'server-id', uuid: 'server-uuid' } as Server);
        store.getActions().schedules.setSchedules([schedule(tasks)]);
    });
};
const deleteTask = async (index: number) => {
    await act(async () => {
        container.querySelectorAll<HTMLButtonElement>('button[aria-label="delete-scheduled-task"]')[index]!.click();
    });
    const confirm = Array.from(container.querySelectorAll('button')).find(
        (button) => button.textContent === 'Confirm deletion',
    )!;
    await act(async () => confirm.click());
};

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.deleteTask.mockResolvedValue(undefined);
    container = document.createElement('div');
    root = createRoot(container);
    await act(async () => {
        root.render(
            <ServerContext.Provider>
                <Rows />
            </ServerContext.Provider>,
        );
    });
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.restoreAllMocks();
});

describe('schedule task deletion', () => {
    it('renumbers the remaining task and hides its ignored delay when the first of two tasks is deleted', async () => {
        await render([task(1), task(2, 60)]);
        expect(container.textContent).toContain('60 seconds later');

        await deleteTask(0);

        expect(mocks.deleteTask).toHaveBeenCalledWith('server-uuid', 1, 1);
        expect(store.getState().schedules.data[0]!.tasks).toEqual([{ ...task(2, 60), sequenceId: 1 }]);
        expect(container.textContent).toContain('say task 2');
        expect(container.textContent).not.toContain('say task 1');
        expect(container.textContent).not.toContain('60 seconds later');
    });

    it('preserves later offsets when the first task is deleted', async () => {
        await render([task(1), task(2, 60), task(3, 90)]);

        await deleteTask(0);

        expect(store.getState().schedules.data[0]!.tasks).toEqual([
            { ...task(2, 60), sequenceId: 1 },
            { ...task(3, 90), sequenceId: 2 },
        ]);
        expect(container.textContent).not.toContain('60 seconds later');
        expect(container.textContent).toContain('90 seconds later');
    });

    it('only shifts tasks after a deleted middle task', async () => {
        await render([task(1), task(2, 60), task(3, 90)]);

        await deleteTask(1);

        expect(store.getState().schedules.data[0]!.tasks).toEqual([task(1), { ...task(3, 90), sequenceId: 2 }]);
        expect(container.textContent).toContain('90 seconds later');
    });

    it('keeps task state and delay badges when deletion fails', async () => {
        const error = new Error('Deletion failed');
        mocks.deleteTask.mockRejectedValueOnce(error);
        vi.spyOn(console, 'error').mockImplementation(() => {});
        await render([task(1), task(2, 60)]);

        await deleteTask(0);

        expect(store.getState().schedules.data[0]!.tasks).toEqual([task(1), task(2, 60)]);
        expect(container.textContent).toContain('60 seconds later');
        expect(mocks.addError).toHaveBeenCalledWith({ message: 'Deletion failed', key: 'schedules' });
    });
});
