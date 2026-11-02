import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import FolderFormModal from './FolderFormModal';
const mocks = vi.hoisted(() => ({
    create: vi.fn(),
    update: vi.fn(),
    saved: vi.fn(),
    dismissed: vi.fn(),
    clear: vi.fn(),
}));
vi.mock('@/api/account/createServerCategory', () => ({ default: mocks.create }));
vi.mock('@/api/account/updateServerCategory', () => ({ default: mocks.update }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: mocks.clear, clearAndAddHttpError: vi.fn() }),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/dialog', () => ({
    Dialog: Object.assign(({ children }: { children: ReactNode }) => children, {
        Footer: ({ children }: { children: ReactNode }) => children,
    }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
let root: Root;
let container: HTMLDivElement;
beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.clearAllMocks();
    mocks.create.mockResolvedValue({});
    mocks.update.mockResolvedValue({});
    mocks.saved.mockResolvedValue(undefined);
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});
it('dedicates creation to the form without folder listings or management controls', async () => {
    await act(async () =>
        root.render(<FolderFormModal parent='parent' onSaved={mocks.saved} onDismissed={mocks.dismissed} />),
    );
    expect(container.querySelectorAll('form')).toHaveLength(1);
    expect(container.textContent).not.toContain('categories.edit');
    expect(container.textContent).not.toContain('categories.delete');
    expect(container.querySelectorAll('select')).toHaveLength(0);
    const input = container.querySelector<HTMLInputElement>('input[name="name"]')!;
    await act(async () => {
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, 'New Folder');
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await act(async () => container.querySelector<HTMLButtonElement>('button[type="submit"]')!.click());
    expect(mocks.create).toHaveBeenCalledWith(
        expect.objectContaining({ name: 'New Folder', parentUuid: 'parent', priority: 0 }),
    );
    expect(mocks.update).not.toHaveBeenCalled();
});
it('edits name, color and priority without changing the parent', async () => {
    await act(async () =>
        root.render(
            <FolderFormModal
                folder={{
                    uuid: 'folder',
                    name: 'Original',
                    parentUuid: 'parent',
                    priority: 7,
                    color: '#123456',
                    description: null,
                    createdAt: new Date(),
                    updatedAt: new Date(),
                }}
                onSaved={mocks.saved}
                onDismissed={mocks.dismissed}
            />,
        ),
    );
    await act(async () => container.querySelector<HTMLButtonElement>('button[type="submit"]')!.click());
    expect(mocks.update).toHaveBeenCalledWith('folder', expect.objectContaining({ priority: 7 }));
    expect(mocks.update.mock.calls[0]![1]).not.toHaveProperty('parentUuid');
    expect(mocks.create).not.toHaveBeenCalled();
});
