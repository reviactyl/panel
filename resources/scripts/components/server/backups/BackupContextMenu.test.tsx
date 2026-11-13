import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { ServerBackup } from '@/api/server/types';
import BackupContextMenu from './BackupContextMenu';

const mocks = vi.hoisted(() => ({ restore: vi.fn(), setServer: vi.fn() }));
vi.mock('@/api/server/backups', () => ({ restoreServerBackup: mocks.restore, renameBackup: vi.fn() }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'server-uuid' } } }),
        useStoreActions: () => mocks.setServer,
    },
}));
vi.mock('@/api/swr/getServerBackups', () => ({ default: () => ({ mutate: vi.fn() }) }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/Fade', () => ({
    default: ({ children, in: visible }: { children: ReactNode; in: boolean }) => (visible ? children : null),
}));
vi.mock('@/reviactyl/elements/dialog', () => ({
    Dialog: {
        Confirm: ({
            open,
            children,
            onClose,
            onConfirmed,
            confirm,
        }: {
            open: boolean;
            children: ReactNode;
            onClose: () => void;
            onConfirmed: () => void;
            confirm?: string;
        }) =>
            open ? (
                <div role='dialog'>
                    {children}
                    <button onClick={onClose}>Cancel</button>
                    <button onClick={onConfirmed}>{confirm}</button>
                </div>
            ) : null,
    },
}));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('./RenameBackupModal', () => ({ default: () => null }));
vi.mock('./BackupPreviewModal', () => ({ default: () => null }));

const backup: ServerBackup = {
    uuid: 'backup-uuid',
    isSuccessful: true,
    isLocked: false,
    name: 'Backup',
    format: 'tar.gz',
    ignoredFiles: '',
    checksum: '',
    bytes: 100,
    createdAt: new Date(),
    completedAt: new Date(),
};
let container: HTMLDivElement;
let root: Root;
beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    vi.useFakeTimers();
    mocks.restore.mockReset().mockResolvedValue(undefined);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
    await act(async () => root.render(<BackupContextMenu backup={backup} />));
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.useRealTimers();
});
const click = async (text: string) => {
    const button = Array.from(container.querySelectorAll('button')).find((button) => button.textContent === text);
    expect(button).toBeDefined();
    await act(async () => button!.click());
    await act(async () => vi.runOnlyPendingTimers());
};
const toggle = () => container.querySelector<HTMLButtonElement>('button[aria-label="backup-actions"]')!;
const openMenu = async () => {
    await act(async () => {
        toggle().focus();
        toggle().click();
    });
    await act(async () => vi.runOnlyPendingTimers());
};
const checkbox = () => container.querySelector<HTMLInputElement>('#restore_truncate')!;
const selectTruncate = async () => {
    await act(async () => checkbox().click());
    expect(checkbox().checked).toBe(true);
};

describe('restore dialog destructive option', () => {
    it('discards a canceled destructive selection before reopening and restoring', async () => {
        await openMenu();
        await click('restore');
        expect(checkbox().checked).toBe(false);
        await selectTruncate();
        await click('Cancel');
        expect(container.querySelector('[role="dialog"]')).toBeNull();
        expect(mocks.restore).not.toHaveBeenCalled();
        expect(mocks.setServer).not.toHaveBeenCalled();

        await openMenu();
        await click('restore');
        expect(checkbox().checked).toBe(false);
        await click('Restore');
        expect(mocks.restore).toHaveBeenCalledExactlyOnceWith('server-uuid', 'backup-uuid', false);
    });

    it('restores with truncation when explicitly selected in the current attempt', async () => {
        await openMenu();
        await click('restore');
        await selectTruncate();
        await click('Restore');
        expect(mocks.restore).toHaveBeenCalledExactlyOnceWith('server-uuid', 'backup-uuid', true);
        expect(mocks.setServer).toHaveBeenCalledTimes(1);
        expect(container.querySelector('[role="dialog"]')).toBeNull();

        await openMenu();
        await click('restore');
        expect(checkbox().checked).toBe(false);
    });
});

describe('completed backup action menu dismissal', () => {
    it('handles Escape before the delayed mouse listeners are installed', async () => {
        await act(async () => toggle().click());
        expect(container.textContent).toContain('delete');
        await act(async () => document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true })));
        expect(container.textContent).not.toContain('delete');
        expect(document.activeElement).toBe(toggle());
    });

    it.each(['trigger', 'action'])('closes on Escape from the %s and restores trigger focus', async (target) => {
        await openMenu();
        const action = Array.from(container.querySelectorAll('button')).find(
            (button) => button.textContent === 'delete',
        )!;
        expect(action).toBeDefined();
        await act(async () => {
            if (target === 'action') action.focus();
            document.activeElement!.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
        });
        expect(container.textContent).not.toContain('delete');
        expect(document.activeElement).toBe(toggle());
    });

    it('closes on an outside mouse click and can reopen', async () => {
        await openMenu();
        await act(async () => document.body.click());
        expect(container.textContent).not.toContain('delete');
        await openMenu();
        expect(container.textContent).toContain('delete');
    });

    it('opens the delete confirmation, closes the menu, and preserves the backup on Cancel', async () => {
        await openMenu();
        await click('delete');
        expect(container.querySelector('[role="dialog"]')).not.toBeNull();
        expect(container.textContent).not.toContain('restore');
        await click('Cancel');
        expect(container.querySelector('[role="dialog"]')).toBeNull();
        expect(toggle()).not.toBeNull();
        await openMenu();
        expect(container.textContent).toContain('delete');
    });
});
