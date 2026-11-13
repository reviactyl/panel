import { act, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { getByLabelText } from '@testing-library/dom';
import type { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import DatabaseRow from './DatabaseRow';

const mocks = vi.hoisted(() => ({
    copy: vi.fn(),
    canViewPassword: true,
    remove: vi.fn(),
    delete: vi.fn(),
    append: vi.fn(),
    addError: vi.fn(),
    clearFlashes: vi.fn(),
}));
vi.mock('copy-to-clipboard', () => ({ default: mocks.copy }));
vi.mock('@/api/server/databases/deleteServerDatabase', () => ({ default: mocks.delete }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'server-uuid' } } }),
        useStoreActions: (selector: (actions: unknown) => unknown) =>
            selector({ databases: { appendDatabase: mocks.append, removeDatabase: mocks.remove } }),
    },
}));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ addError: mocks.addError, clearFlashes: mocks.clearFlashes, addFlash: vi.fn() }),
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

vi.mock('@/components/server/databases/RotatePasswordButton', () => ({ default: () => null }));
vi.mock('@/components/server/databases/ImportDatabaseModal', () => ({ default: () => null }));
vi.mock('@/components/server/databases/ExportDatabaseModal', () => ({ default: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('@/reviactyl/elements/Can', () => ({
    default: ({ action, children }: { action: string; children: ReactNode }) =>
        action === 'database.view_password' && !mocks.canViewPassword ? null : children,
}));

let portal: HTMLDivElement;
let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.canViewPassword = true;
    mocks.delete.mockResolvedValue(undefined);
    container = document.createElement('div');
    document.body.append(container);
    portal = document.createElement('div');
    portal.id = 'modal-portal';
    document.body.append(portal);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    portal.remove();
});

it.each([
    ['s1_minecraft_world', 'minecraft_world', true],
    ['s1_minecraft_world', 's1_minecraft_world', true],
    ['s1_minecraft_world', 'minecraft', false],
    ['s1_minecraft_world_backup', 'minecraft_world_backup', true],
    ['s1_minecraft_world_backup', 'minecraft_world', false],
    ['s1_minecraft', 'minecraft', true],
    ['s1_minecraft', 's1_minecraft', true],
    ['minecraft', 'minecraft', true],
    ['s1_minecraft_world', '', false],
])('confirms %s with %s only when complete (%s)', async (name, confirmation, valid) => {
    const database: ServerDatabase = {
        id: 'database-id',
        name,
        username: 'user',
        connectionString: 'localhost:3306',
        allowConnectionsFrom: '%',
    };
    await act(async () => root.render(<DatabaseRow database={database} />));
    await act(async () => container.querySelector<HTMLButtonElement>('button[title="delete-database"]')!.click());
    const input = container.querySelector<HTMLInputElement>('input[name="confirm"]')!;
    await act(async () => {
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, confirmation);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    const submit = container.querySelector<HTMLButtonElement>('button[type="submit"]')!;
    expect(submit.disabled).toBe(!valid);
    await act(async () =>
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
    );
    if (valid) expect(mocks.delete).toHaveBeenCalledWith('server-uuid', 'database-id');
    else expect(mocks.delete).not.toHaveBeenCalled();
});

const database: ServerDatabase = {
    id: '1',
    name: 's1_shop',
    username: 'shop_user',
    password: 'test-password',
    connectionString: 'localhost:3306',
    allowConnectionsFrom: '%',
};
const values = {
    endpoint: database.connectionString,
    'connections-from': database.allowConnectionsFrom,
    username: database.username,
    password: database.password,
    'jdbc-connection-string': 'jdbc:mysql://shop_user:test-password@localhost:3306/s1_shop',
};

const openAll = async () => {
    for (const button of container.querySelectorAll<HTMLButtonElement>('button[aria-label="connection-title"]')) {
        await act(async () => button.click());
    }
};

describe('database connection labels', () => {
    it('associates all values with their visible labels and preserves copying', async () => {
        await act(async () => root.render(<DatabaseRow database={database} />));
        await openAll();
        const dialog = container.querySelector<HTMLElement>('[role="dialog"]')!;
        for (const [label, value] of Object.entries(values)) {
            const input = getByLabelText<HTMLInputElement>(dialog, label);
            expect(input.value).toBe(value);
            expect(input.readOnly).toBe(true);
            await act(async () => input.click());
            if (label !== 'connections-from') expect(mocks.copy).toHaveBeenLastCalledWith(value);
        }
        expect(mocks.copy).toHaveBeenCalledTimes(4);
    });

    it('keeps associations unique across row instances, even for the same database', async () => {
        await act(async () =>
            root.render(
                <>
                    <DatabaseRow database={database} />
                    <DatabaseRow database={database} />
                </>,
            ),
        );
        await openAll();
        const ids: string[] = [];
        for (const dialog of container.querySelectorAll<HTMLElement>('[role="dialog"]')) {
            for (const label of Object.keys(values)) ids.push(getByLabelText<HTMLInputElement>(dialog, label).id);
        }
        expect(new Set(ids).size).toBe(10);
    });

    it('preserves password permissions and the Close action', async () => {
        mocks.canViewPassword = false;
        await act(async () => root.render(<DatabaseRow database={database} />));
        await openAll();
        const dialog = container.querySelector<HTMLElement>('[role="dialog"]')!;
        expect(dialog.querySelectorAll('input')).toHaveLength(4);
        expect([...dialog.querySelectorAll('label')].some((label) => label.textContent === 'password')).toBe(false);
        const close = [...dialog.querySelectorAll('button')].find((button) => button.textContent === 'close')!;
        await act(async () => close.click());
        expect(container.querySelector('[role="dialog"]')).toBeNull();
    });
});
