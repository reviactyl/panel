import { act, type ButtonHTMLAttributes, type ReactNode } from 'react';
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
vi.mock('@/reviactyl/elements/DropdownMenu', () => ({
    default: ({ children }: { children: ReactNode }) => children,
    DropdownButtonRow: ({ children, onClick }: ButtonHTMLAttributes<HTMLButtonElement>) => (
        <button onClick={onClick}>{children}</button>
    ),
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
    mocks.restore.mockReset().mockResolvedValue(undefined);
    container = document.createElement('div');
    root = createRoot(container);
    await act(async () => root.render(<BackupContextMenu backup={backup} />));
});
afterEach(async () => {
    await act(async () => root.unmount());
});
const click = async (text: string) => {
    const button = Array.from(container.querySelectorAll('button')).find((button) => button.textContent === text);
    expect(button).toBeDefined();
    await act(async () => button!.click());
};
const checkbox = () => container.querySelector<HTMLInputElement>('#restore_truncate')!;
const selectTruncate = async () => {
    await act(async () => checkbox().click());
    expect(checkbox().checked).toBe(true);
};

describe('restore dialog destructive option', () => {
    it('discards a canceled destructive selection before reopening and restoring', async () => {
        await click('restore');
        expect(checkbox().checked).toBe(false);
        await selectTruncate();
        await click('Cancel');
        expect(container.querySelector('[role="dialog"]')).toBeNull();
        expect(mocks.restore).not.toHaveBeenCalled();
        expect(mocks.setServer).not.toHaveBeenCalled();

        await click('restore');
        expect(checkbox().checked).toBe(false);
        await click('Restore');
        expect(mocks.restore).toHaveBeenCalledExactlyOnceWith('server-uuid', 'backup-uuid', false);
    });

    it('restores with truncation when explicitly selected in the current attempt', async () => {
        await click('restore');
        await selectTruncate();
        await click('Restore');
        expect(mocks.restore).toHaveBeenCalledExactlyOnceWith('server-uuid', 'backup-uuid', true);
        expect(mocks.setServer).toHaveBeenCalledTimes(1);
        expect(container.querySelector('[role="dialog"]')).toBeNull();

        await click('restore');
        expect(checkbox().checked).toBe(false);
    });
});
