import { act, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import CreateDatabaseButton, { databaseCreationSchema } from './CreateDatabaseButton';
import databaseNames from '../../../../../tests/Fixtures/database-names.json';

const mocks = vi.hoisted(() => ({ create: vi.fn(), append: vi.fn(), addError: vi.fn(), clearFlashes: vi.fn() }));
vi.mock('@/api/server/databases/createServerDatabase', () => ({ default: mocks.create }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'server-uuid' } } }),
        useStoreActions: (selector: (actions: unknown) => unknown) =>
            selector({ databases: { appendDatabase: mocks.append } }),
    },
}));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ addError: mocks.addError, clearFlashes: mocks.clearFlashes }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({
        visible,
        showSpinnerOverlay,
        children,
    }: {
        visible: boolean;
        showSpinnerOverlay: boolean;
        children: ReactNode;
    }) =>
        visible ? (
            <div role='dialog' aria-busy={showSpinnerOverlay}>
                {children}
            </div>
        ) : null,
}));
vi.mock('@/reviactyl/elements/Button', () => ({
    default: ({
        isSecondary: _secondary,
        ...props
    }: ButtonHTMLAttributes<HTMLButtonElement> & { isSecondary?: boolean }) => <button {...props} />,
}));

const database: ServerDatabase = {
    id: '1',
    name: 's1_first',
    username: 'user',
    connectionString: 'localhost:3306',
    allowConnectionsFrom: '%',
};
let container: HTMLDivElement;
let root: Root;

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.create.mockReset().mockResolvedValue(database);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
    await act(async () => root.render(<CreateDatabaseButton />));
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

const input = (name: string) => container.querySelector<HTMLInputElement>(`input[name="${name}"]`)!;
const dialog = () => container.querySelector('[role="dialog"]');
const click = async (label: string) => {
    const button = Array.from(container.querySelectorAll('button')).find((element) => element.textContent === label);
    expect(button).toBeDefined();
    await act(async () => button!.click());
};
const fill = async (name: string, value: string) => {
    await act(async () => {
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input(name), value);
        input(name).dispatchEvent(new Event('input', { bubbles: true }));
    });
};
const submit = async () => {
    await act(async () =>
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
    );
};

describe('database creation form lifecycle', () => {
    it('creates two databases consecutively without remounting and reopens with a fresh idle form', async () => {
        let finish!: (result: ServerDatabase) => void;
        mocks.create.mockReturnValueOnce(new Promise<ServerDatabase>((resolve) => (finish = resolve)));
        await click('new-database');
        await fill('databaseName', 'first');
        await fill('connectionsFrom', '10.0.0.%');
        await submit();
        expect(dialog()?.getAttribute('aria-busy')).toBe('true');
        expect(mocks.append).not.toHaveBeenCalled();
        await act(async () => finish(database));
        expect(mocks.append).toHaveBeenCalledWith(database);
        expect(dialog()).toBeNull();

        await click('new-database');
        expect(dialog()?.getAttribute('aria-busy')).toBe('false');
        expect(input('databaseName').value).toBe('');
        expect(input('connectionsFrom').value).toBe('');
        await fill('databaseName', 'second');
        const second = { ...database, id: '2', name: 's1_second' };
        mocks.create.mockResolvedValueOnce(second);
        await submit();
        expect(mocks.create.mock.calls).toEqual([
            ['server-uuid', { databaseName: 'first', connectionsFrom: '10.0.0.%' }],
            ['server-uuid', { databaseName: 'second', connectionsFrom: '%' }],
        ]);
        expect(mocks.append).toHaveBeenNthCalledWith(2, second);
        expect(dialog()).toBeNull();
        await click('new-database');
        expect(dialog()?.getAttribute('aria-busy')).toBe('false');
        expect(input('databaseName').value).toBe('');
        expect(input('connectionsFrom').value).toBe('');
    });

    it.each([
        ['databaseName', 'ab', 'Database name must be at least 3 characters.'],
        ['connectionsFrom', 'invalid host', 'A valid host address must be provided.'],
        [
            'databaseName',
            'minecraft.world',
            'Database name should only contain alphanumeric characters, underscores, and/or dashes.',
        ],
    ])('allows correcting %s after validation fails', async (name, invalid, message) => {
        await click('new-database');
        await fill('databaseName', 'valid');
        await fill(name, invalid);
        await submit();
        expect(mocks.create).not.toHaveBeenCalled();
        expect(container.textContent).toContain(message);
        expect(dialog()?.getAttribute('aria-busy')).toBe('false');
        await fill(name, name === 'databaseName' ? 'corrected' : 'localhost');
        await submit();
        expect(mocks.create).toHaveBeenCalledTimes(1);
        expect(mocks.append).toHaveBeenCalledWith(database);
        expect(dialog()).toBeNull();
    });

    it('preserves failed values and allows correcting and retrying an API error', async () => {
        mocks.create.mockRejectedValueOnce(new Error('Database creation failed.'));
        await click('new-database');
        await fill('databaseName', 'original');
        await fill('connectionsFrom', 'localhost');
        await submit();
        expect(mocks.addError).toHaveBeenCalledWith({ key: 'database:create', message: 'Database creation failed.' });
        expect(dialog()?.getAttribute('aria-busy')).toBe('false');
        expect(input('databaseName').value).toBe('original');
        expect(input('connectionsFrom').value).toBe('localhost');
        expect(mocks.append).not.toHaveBeenCalled();
        await fill('databaseName', 'retried');
        await submit();
        expect(mocks.create).toHaveBeenLastCalledWith('server-uuid', {
            databaseName: 'retried',
            connectionsFrom: 'localhost',
        });
        expect(mocks.clearFlashes).toHaveBeenNthCalledWith(2, 'database:create');
        expect(mocks.append).toHaveBeenCalledTimes(1);
        expect(dialog()).toBeNull();
    });
});

describe('database naming requirements shared with StoreDatabaseRequest', () => {
    it.each(databaseNames)('validates "$name" as $valid', ({ name, valid }) => {
        expect(databaseCreationSchema.isValidSync({ databaseName: name, connectionsFrom: '' })).toBe(valid);
    });

    it.each(['minecraft_world', 'minecraft-world'])('creates the valid name %s', async (name) => {
        await click('new-database');
        await fill('databaseName', name);
        await submit();

        expect(mocks.create).toHaveBeenCalledWith('server-uuid', {
            databaseName: name,
            connectionsFrom: '%',
        });
        expect(dialog()).toBeNull();
    });
});
