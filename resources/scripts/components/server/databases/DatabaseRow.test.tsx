import { act, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import type { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import DatabaseRow from './DatabaseRow';

const mocks = vi.hoisted(() => ({
    remove: vi.fn(),
    delete: vi.fn(),
    append: vi.fn(),
    addError: vi.fn(),
    clearFlashes: vi.fn(),
}));
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
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/CopyOnClick', () => ({ default: ({ children }: { children: ReactNode }) => children }));

let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.delete.mockResolvedValue(undefined);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
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
