import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Server } from '@/api/server/getServer';
import type { Schedule } from '@/api/server/schedules/getServerSchedules';
import { ServerContext } from '@/state/server';
import { formatInTimezone } from '@/lib/timezones';
import ScheduleEditContainer from './ScheduleEditContainer';

const mocks = vi.hoisted(() => ({
    fetch: vi.fn(),
    execute: vi.fn(),
    scheduleId: '1',
    clearFlashes: vi.fn(),
    clearAndAddHttpError: vi.fn(),
    addError: vi.fn(),
}));
vi.mock('@/api/server/schedules/getServerSchedule', () => ({ default: mocks.fetch }));
vi.mock('@/api/server/schedules/triggerScheduleExecution', () => ({ default: mocks.execute }));
vi.mock('react-router-dom', () => ({
    useParams: () => ({ id: mocks.scheduleId }),
    useNavigate: () => vi.fn(),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/plugins/useFlash', () => ({ default: () => mocks }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/Spinner', () => ({ default: () => <span>Loading</span> }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('./EditScheduleModal', () => ({ default: () => null }));
vi.mock('./NewTaskButton', () => ({ default: () => null }));
vi.mock('./DeleteScheduleButton', () => ({ default: () => null }));
vi.mock('./ScheduleTaskRow', () => ({
    default: ({ task }: { task: Schedule['tasks'][number] }) => <span>{task.payload}</span>,
}));
vi.mock('./ScheduleStatus', () => ({
    default: ({ active, processing }: { active: boolean; processing: boolean }) => (
        <span>{processing ? 'Processing' : active ? 'Active' : 'Inactive'}</span>
    ),
}));

const cached: Schedule = {
    id: 1,
    name: 'Daily commands',
    cron: { minute: '0', hour: '12', dayOfMonth: '*', month: '*', dayOfWeek: '*' },
    isActive: true,
    isProcessing: false,
    onlyWhenOnline: false,
    lastRunAt: new Date('2026-11-01T12:00:00Z'),
    nextRunAt: new Date('2026-11-02T12:00:00Z'),
    createdAt: new Date('2026-11-01T00:00:00Z'),
    updatedAt: new Date('2026-11-01T00:00:00Z'),
    tasks: [
        {
            id: 1,
            sequenceId: 1,
            action: 'command',
            payload: 'say old',
            timeOffset: 0,
            isQueued: false,
            continueOnFailure: false,
            createdAt: new Date('2026-11-01T00:00:00Z'),
            updatedAt: new Date('2026-11-01T00:00:00Z'),
        },
    ],
};
const updated: Schedule = {
    ...cached,
    isActive: false,
    cron: { ...cached.cron, minute: '30' },
    lastRunAt: new Date('2026-11-03T12:00:00Z'),
    nextRunAt: null,
    tasks: [{ ...cached.tasks[0]!, id: 2, payload: 'say new' }],
};

let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof ServerContext.useStore>;
const CaptureStore = () => {
    store = ServerContext.useStore();
    return null;
};
const render = async (visible = true) => {
    await act(async () => {
        root.render(
            <ServerContext.Provider>
                <CaptureStore />
                {visible && <ScheduleEditContainer />}
            </ServerContext.Provider>,
        );
    });
};
const deferred = () => {
    let resolve!: (schedule: Schedule) => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<Schedule>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
};

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    vi.clearAllMocks();
    mocks.fetch.mockReset();
    mocks.execute.mockResolvedValue({ skipped: false });
    mocks.scheduleId = '1';
    container = document.createElement('div');
    root = createRoot(container);
    await render(false);
    act(() => {
        store.getActions().server.setServer({ id: 'server-id', uuid: 'server-uuid', timezone: 'UTC' } as Server);
        store.getActions().schedules.setSchedules([cached]);
    });
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.useRealTimers();
    vi.restoreAllMocks();
});

describe('schedule detail revalidation', () => {
    it('refreshes cached details after leaving and reopening without loading the list', async () => {
        mocks.fetch.mockResolvedValueOnce(cached);
        await render();
        expect(container.textContent).toContain(formatInTimezone(cached.lastRunAt!, 'UTC'));
        await render(false);

        const request = deferred();
        mocks.fetch.mockReturnValueOnce(request.promise);
        await render();
        expect(mocks.fetch).toHaveBeenCalledTimes(2);
        expect(mocks.fetch).toHaveBeenLastCalledWith('server-uuid', 1);
        expect(container.textContent).toContain('say old');
        expect(container.textContent).not.toContain('Loading');

        await act(async () => request.resolve(updated));
        expect(store.getState().schedules.data).toEqual([updated]);
        expect(container.textContent).toContain(formatInTimezone(updated.lastRunAt!, 'UTC'));
        expect(container.textContent).not.toContain(formatInTimezone(cached.nextRunAt!, 'UTC'));
        expect(container.textContent).toContain('Inactive');
        expect(container.textContent).toContain('say new');
        expect(container.textContent).not.toContain('say old');
        expect(mocks.fetch).toHaveBeenCalledTimes(2);
    });

    it('shows loading without a cached record and renders the fetched schedule', async () => {
        act(() => store.getActions().schedules.setSchedules([]));
        const request = deferred();
        mocks.fetch.mockReturnValueOnce(request.promise);
        await render();
        expect(container.textContent).toBe('Loading');
        await act(async () => request.resolve(updated));
        expect(container.textContent).toContain('say new');
        expect(container.textContent).not.toContain('Loading');
    });

    it('keeps cached details and reports a failed refresh', async () => {
        const error = new Error('Refresh failed');
        vi.spyOn(console, 'error').mockImplementation(() => {});
        mocks.fetch.mockRejectedValueOnce(error);
        await render();
        expect(container.textContent).toContain('say old');
        expect(mocks.clearAndAddHttpError).toHaveBeenCalledWith({ error, key: 'schedules' });
    });

    it('ignores a response after leaving so it cannot overwrite the reopened record', async () => {
        const request = deferred();
        mocks.fetch.mockReturnValueOnce(request.promise).mockResolvedValueOnce(updated);
        await render();
        await render(false);
        await render();
        await act(async () => request.resolve(cached));
        expect(store.getState().schedules.data).toEqual([updated]);
        expect(container.textContent).toContain('say new');
    });

    it('refreshes when the server changes and ignores the previous server response', async () => {
        const request = deferred();
        mocks.fetch.mockReturnValueOnce(request.promise).mockResolvedValueOnce(updated);
        await render();
        await act(async () => {
            store.getActions().server.setServerFromState((server) => ({
                ...server,
                id: 'other-id',
                uuid: 'other-uuid',
            }));
        });
        expect(mocks.fetch).toHaveBeenLastCalledWith('other-uuid', 1);
        await act(async () => request.resolve(cached));
        expect(store.getState().schedules.data).toEqual([updated]);
    });

    it('refreshes a different route and ignores failures from the previous schedule', async () => {
        const request = deferred();
        const second = { ...updated, id: 2, name: 'Second schedule' };
        act(() => store.getActions().schedules.appendSchedule(second));
        mocks.fetch.mockReturnValueOnce(request.promise).mockResolvedValueOnce(second);
        await render();
        mocks.scheduleId = '2';
        await render();
        expect(mocks.fetch).toHaveBeenLastCalledWith('server-uuid', 2);
        expect(container.textContent).toContain('Second schedule');
        await act(async () => request.reject(new Error('Old request failed')));
        expect(mocks.clearAndAddHttpError).not.toHaveBeenCalled();
        expect(container.textContent).toContain('Second schedule');
    });

    it('continues Run Now polling until the refreshed schedule finishes', async () => {
        const processing = { ...cached, isProcessing: true };
        mocks.fetch.mockResolvedValueOnce(cached).mockResolvedValueOnce(processing).mockResolvedValueOnce(updated);
        await render();
        const run = Array.from(container.querySelectorAll('button')).find(
            (button) => button.textContent === 'Run Now',
        )!;
        await act(async () => run.click());
        expect(mocks.execute).toHaveBeenCalledWith('server-id', 1);
        expect(run.disabled).toBe(true);
        expect(container.textContent).toContain('Processing');
        await act(async () => vi.advanceTimersByTime(2000));
        expect(mocks.fetch).toHaveBeenCalledTimes(3);
        expect(run.disabled).toBe(false);
        expect(container.textContent).toContain('say new');
        expect(container.textContent).not.toContain('Processing');
    });

    it('keeps newer Run Now results when a slow entry refresh finishes last', async () => {
        const request = deferred();
        mocks.fetch
            .mockReturnValueOnce(request.promise)
            .mockResolvedValueOnce({ ...cached, isProcessing: true })
            .mockResolvedValueOnce(updated);
        await render();
        const run = Array.from(container.querySelectorAll('button')).find(
            (button) => button.textContent === 'Run Now',
        )!;
        await act(async () => run.click());
        await act(async () => vi.advanceTimersByTime(2000));
        expect(store.getState().schedules.data).toEqual([updated]);
        await act(async () => request.resolve(cached));
        expect(store.getState().schedules.data).toEqual([updated]);
        expect(container.textContent).toContain('say new');
    });
});
