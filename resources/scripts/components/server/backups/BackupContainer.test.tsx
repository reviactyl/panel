import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { SWRConfig } from 'swr';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { ServerBackup } from '@/api/server/types';
import type { PaginatedResult } from '@/api/http';
import BackupContainer from './BackupContainer';

const mocks = vi.hoisted(() => ({
    fetch: vi.fn(),
    completed: undefined as ((data: string) => void) | undefined,
}));

type BackupResponse = PaginatedResult<ServerBackup> & { backupCount: number };

vi.mock('@/api/swr/getServerBackups', async () => {
    const { createContext, useContext } = await import('react');
    const { default: useSWR } = await import('swr');
    const Context = createContext({ page: 1, setPage: (_page: number) => {} });
    return {
        Context,
        default: () => {
            const { page } = useContext(Context);
            return useSWR<BackupResponse>(['backups', page], () => mocks.fetch(page));
        },
    };
});
vi.mock('@/state/server', () => ({
    ServerContext: { useStoreState: () => 1 },
}));
vi.mock('@/plugins/useWebsocketEvent', () => ({
    default: (_event: string, callback: (data: string) => void) => {
        mocks.completed = callback;
    },
}));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }),
}));
vi.mock('react-i18next', () => ({
    useTranslation: () => ({
        t: (key: string, options?: { count: number; limit: number }) =>
            options ? `${options.count} of ${options.limit}` : key,
    }),
}));
vi.mock('@/reviactyl/elements/Spinner', () => ({ default: () => <span>Loading</span> }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/Pagination', () => ({
    default: ({
        data,
        children,
        onPageSelect,
    }: {
        data: BackupResponse;
        children: (data: BackupResponse) => ReactNode;
        onPageSelect: (page: number) => void;
    }) => (
        <>
            <a href='#page-3' onClick={() => onPageSelect(3)}>
                Page 3
            </a>
            {children(data)}
        </>
    ),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('./CreateBackupButton', () => ({ default: () => <button>Create backup</button> }));
vi.mock('./BackupRow', () => ({
    default: ({ backup }: { backup: ServerBackup }) => (
        <span>{backup.completedAt ? (backup.isSuccessful ? 'Successful' : 'Failed') : 'Pending'}</span>
    ),
}));

const pending: ServerBackup = {
    uuid: 'backup-1',
    isSuccessful: false,
    isLocked: false,
    name: 'Backup',
    format: 'tar.gz',
    ignoredFiles: '',
    checksum: '',
    bytes: 0,
    createdAt: new Date(),
    completedAt: null,
};
const response = (items = [pending], backupCount = 1): BackupResponse => ({
    items,
    backupCount,
    pagination: { total: items.length, count: items.length, perPage: 20, currentPage: 1, totalPages: 1 },
});
let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.fetch.mockReset();
    mocks.completed = undefined;
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
});
const render = async () => {
    await act(async () => {
        root.render(
            <SWRConfig value={{ provider: () => new Map(), dedupingInterval: 0 }}>
                <BackupContainer />
            </SWRConfig>,
        );
    });
};
const complete = async (event: object) => {
    await act(async () => {
        mocks.completed!(JSON.stringify(event));
    });
};

describe('backup completion slot count', () => {
    it('restores Create Backup after a failed backup fills the last slot', async () => {
        mocks.fetch
            .mockResolvedValueOnce(response())
            .mockResolvedValue(response([{ ...pending, completedAt: new Date() }], 0));
        await render();
        expect(container.textContent).toContain('1 of 1');
        expect(container.querySelector('button')).toBeNull();
        await complete({ uuid: pending.uuid, is_successful: false });
        expect(container.textContent).toContain('Failed');
        expect(container.textContent).not.toContain('1 of 1');
        expect(container.querySelector('button')?.textContent).toBe('Create backup');
        expect(mocks.fetch).toHaveBeenCalledTimes(2);
        await complete({ uuid: pending.uuid, is_successful: false });
        expect(container.querySelector('button')).not.toBeNull();
        expect(container.textContent).not.toContain('-1');
    });

    it('refreshes the global count when the failed backup is outside the visible page', async () => {
        mocks.fetch.mockResolvedValueOnce(response([], 1)).mockResolvedValue(response([], 0));
        await render();
        await complete({ uuid: 'another-page', is_successful: false });
        expect(mocks.fetch).toHaveBeenCalledTimes(2);
        expect(container.querySelector('button')).not.toBeNull();
    });

    it.each([true, undefined])(
        'keeps successful backups counted, including older Wings events (%s)',
        async (isSuccessful) => {
            mocks.fetch.mockResolvedValue(response());
            await render();
            await complete({ uuid: pending.uuid, is_successful: isSuccessful });
            expect(container.textContent).toContain('Successful');
            expect(container.textContent).toContain('1 of 1');
            expect(container.querySelector('button')).toBeNull();
            expect(mocks.fetch).toHaveBeenCalledTimes(1);
        },
    );

    it('refetches when completion arrives during the first load', async () => {
        let resolveFirst!: (data: BackupResponse) => void;
        mocks.fetch
            .mockImplementationOnce(
                () =>
                    new Promise<BackupResponse>((resolve) => {
                        resolveFirst = resolve;
                    }),
            )
            .mockResolvedValue(response([{ ...pending, completedAt: new Date() }], 0));
        await render();
        expect(container.textContent).toBe('Loading');
        await complete({ uuid: pending.uuid, is_successful: false });
        await act(async () => resolveFirst(response()));
        expect(mocks.fetch).toHaveBeenCalledTimes(2);
        expect(container.textContent).toContain('Failed');
        expect(container.querySelector('button')).not.toBeNull();
    });
});

describe('backup pagination recovery', () => {
    it.each([2, 1])('returns an empty page to the last available page (%s)', async (totalPages) => {
        mocks.fetch.mockImplementation(async (page: number) => ({
            ...response(page === 3 ? [] : [pending]),
            pagination: { total: 21, count: page === 3 ? 0 : 1, perPage: 20, currentPage: page, totalPages },
        }));
        await render();
        await act(async () => container.querySelector('a')!.click());
        expect(mocks.fetch.mock.calls.map(([page]) => page)).toEqual([1, 3, totalPages]);
        expect(container.textContent).toContain('Pending');
        expect(container.textContent).not.toContain('out-of-backups');
    });

    it('keeps a nonempty later page selected', async () => {
        mocks.fetch.mockImplementation(async (page: number) => ({
            ...response(),
            pagination: { total: 41, count: 1, perPage: 20, currentPage: page, totalPages: 3 },
        }));
        await render();
        await act(async () => container.querySelector('a')!.click());
        expect(mocks.fetch.mock.calls.map(([page]) => page)).toEqual([1, 3]);
        expect(container.textContent).toContain('Pending');
    });

    it('keeps an empty first page selected without refetching', async () => {
        mocks.fetch.mockResolvedValue(response([], 0));
        await render();
        expect(mocks.fetch).toHaveBeenCalledTimes(1);
        expect(container.textContent).toContain('no-backups');
    });
});
