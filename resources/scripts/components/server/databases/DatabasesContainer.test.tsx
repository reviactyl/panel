import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import DatabasesContainer from './DatabasesContainer';

const mocks = vi.hoisted(() => ({
    limit: null as number | null,
    databases: [] as ServerDatabase[],
    canCreate: true,
    fetch: vi.fn(),
    setDatabases: vi.fn(),
}));

vi.mock('@/api/server/databases/getServerDatabases', () => ({ default: mocks.fetch }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({
                server: { data: { uuid: 'server-uuid', featureLimits: { databases: mocks.limit } } },
                databases: { data: mocks.databases },
            }),
        useStoreActions: (selector: (actions: unknown) => unknown) =>
            selector({ databases: { setDatabases: mocks.setDatabases } }),
    },
}));
vi.mock('@/plugins/useFlash', () => ({ default: () => ({ addError: vi.fn(), clearFlashes: vi.fn() }) }));
vi.mock('react-i18next', () => ({
    useTranslation: () => ({
        t: (key: string, options?: { count: number; limit: number }) =>
            options ? `${options.count} of ${options.limit}` : key,
    }),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('@/reviactyl/elements/Can', () => ({
    default: ({ children }: { children: ReactNode }) => (mocks.canCreate ? children : null),
}));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/Fade', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('./DatabaseRow', () => ({
    default: ({ database }: { database: ServerDatabase }) => <div>{database.name}</div>,
}));
vi.mock('./CreateDatabaseButton', () => ({ default: () => <button>New Database</button> }));

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.limit = null;
    mocks.databases = [];
    mocks.canCreate = true;
    mocks.fetch.mockImplementation(async () => mocks.databases);
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
});

describe('database creation allowance', () => {
    it.each([
        { limit: null, count: 0, canCreate: true },
        { limit: null, count: 3, canCreate: true },
        { limit: 0, count: 0, canCreate: false },
        { limit: 0, count: 1, canCreate: false },
        { limit: 2, count: 0, canCreate: true },
        { limit: 2, count: 1, canCreate: true },
        { limit: 2, count: 2, canCreate: false },
        { limit: 2, count: 3, canCreate: false },
    ])('renders limit=$limit with $count databases correctly', async ({ limit, count, canCreate }) => {
        mocks.limit = limit;
        mocks.databases = Array.from({ length: count }, (_, index) => ({
            id: String(index),
            name: `s1_database_${index}`,
            username: 'user',
            connectionString: 'localhost:3306',
            allowConnectionsFrom: '%',
        }));
        await act(async () => root.render(<DatabasesContainer />));

        const button = container.querySelector('button');
        expect(Boolean(button)).toBe(canCreate);
        if (canCreate) expect(button?.disabled).toBe(false);
        if (count === 0) {
            expect(container.textContent).toContain(limit === 0 ? 'no-databases' : 'out-of-databases');
        }
        if (limit !== null && limit > 0 && count > 0) {
            expect(container.textContent).toContain(`${count} of ${limit}`);
        } else {
            expect(container.textContent).not.toContain(' of ');
        }
        expect(mocks.fetch).toHaveBeenCalledWith('server-uuid');
    });

    it('keeps creation permission required for unlimited plans', async () => {
        mocks.canCreate = false;
        await act(async () => root.render(<DatabasesContainer />));
        expect(container.querySelector('button')).toBeNull();
    });
});
