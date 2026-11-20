import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import type { ServerBackup } from '@/api/server/types';
import BackupContextMenu from './BackupContextMenu';

const mocks = vi.hoisted(() => ({ post: vi.fn(), clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }));
vi.mock('@/api/http', () => ({ default: { post: mocks.post } }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'server-uuid' } } }),
        useStoreActions: () => ({}),
    },
}));
vi.mock('@/api/swr/getServerBackups', () => ({ default: () => ({ mutate: vi.fn() }) }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: mocks.clearFlashes, clearAndAddHttpError: mocks.clearAndAddHttpError }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/components/Can', () => ({ default: ({ children }: { children: unknown }) => children }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: unknown }) => children }));
vi.mock('@/reviactyl/elements/Fade', () => ({
    default: ({ children, in: visible }: { children: ReactNode; in: boolean }) => (visible ? children : null),
}));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('./RenameBackupModal', () => ({ default: () => null }));
vi.mock('./BackupPreviewModal', () => ({ default: () => null }));

const backup: ServerBackup = {
    uuid: 'backup-uuid',
    isSuccessful: true,
    isLocked: true,
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
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.clearAllMocks();
    vi.useFakeTimers();
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

it('shows progress in the real unlock dialog and disables confirmation', async () => {
    let resolvePost!: () => void;
    mocks.post.mockReturnValueOnce(new Promise<void>((resolve) => (resolvePost = resolve)));

    const toggle = container.querySelector<HTMLButtonElement>('button[aria-label="backup-actions"]')!;
    await act(async () => toggle.click());
    await act(async () => vi.runOnlyPendingTimers());
    const unlock = Array.from(document.querySelectorAll('button')).find((button) => button.textContent === 'unlock')!;
    await act(async () => unlock.click());
    await act(async () => Promise.resolve());

    const dialog = document.querySelector('[role="dialog"]')!;
    const confirm = Array.from(dialog.querySelectorAll('button')).find((button) => button.textContent === 'Okay')!;
    await act(async () => confirm.click());

    expect(confirm).toHaveProperty('disabled', true);
    expect(confirm.getAttribute('aria-busy')).toBe('true');
    expect(confirm.querySelector('.animate-spin')).not.toBeNull();
    expect(document.querySelector('.fixed .animate-spin')).not.toBeNull();

    resolvePost();
});
