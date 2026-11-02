import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import CategoryManagerModal from './CategoryManagerModal';

const mocks = vi.hoisted(() => ({
    getCategories: vi.fn(),
    deleteCategory: vi.fn(),
    createCategory: vi.fn(),
    updateCategory: vi.fn(),
    changed: vi.fn(),
    dismissed: vi.fn(),
    clear: vi.fn(),
    error: vi.fn(),
}));
vi.mock('@/api/account/getServerCategories', () => ({ default: mocks.getCategories }));
vi.mock('@/api/account/deleteServerCategory', () => ({ default: mocks.deleteCategory }));
vi.mock('@/api/account/createServerCategory', () => ({ default: mocks.createCategory }));
vi.mock('@/api/account/updateServerCategory', () => ({ default: mocks.updateCategory }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: mocks.clear, clearAndAddHttpError: mocks.error }),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({ visible, children }: { visible: boolean; children: ReactNode }) =>
        visible ? (
            <section role='dialog' aria-label='Category Manager'>
                {children}
            </section>
        ) : null,
}));
vi.mock('@/reviactyl/elements/dialog', () => ({
    Dialog: Object.assign(
        ({ title, children }: { title: string; children: ReactNode }) => (
            <section role='dialog' aria-label={title}>
                {children}
            </section>
        ),
        { Footer: ({ children }: { children: ReactNode }) => children },
    ),
}));
let container: HTMLDivElement;
let root: Root;
beforeEach(async () => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.clearAllMocks();
    mocks.getCategories.mockResolvedValue([{ uuid: 'folder', name: 'Small' }]);
    mocks.deleteCategory.mockResolvedValue(undefined);
    mocks.createCategory.mockResolvedValue({});
    mocks.updateCategory.mockResolvedValue({});
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    await act(async () =>
        root.render(<CategoryManagerModal visible onDismissed={mocks.dismissed} onCategoryChanged={mocks.changed} />),
    );
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});
const openDelete = async () => {
    await act(async () =>
        container.querySelector<HTMLButtonElement>('[aria-label="categories.delete: Small"]')!.click(),
    );
    expect(container.querySelector('[aria-label="Category Manager"]')).toBeNull();
    expect(container.querySelectorAll('[role="dialog"]')).toHaveLength(1);
    expect(container.querySelector('[aria-label="categories.delete: Small"]')).not.toBeNull();
};
it('shows only the confirmation while deleting and restores the manager on cancel', async () => {
    await openDelete();
    await act(async () =>
        Array.from(container.querySelectorAll('button'))
            .find((button) => button.textContent === 'cancel')!
            .click(),
    );
    expect(container.querySelector('[aria-label="Category Manager"]')).not.toBeNull();
    expect(container.querySelectorAll('[role="dialog"]')).toHaveLength(1);
    expect(mocks.deleteCategory).not.toHaveBeenCalled();
    expect(mocks.dismissed).not.toHaveBeenCalled();
});
it('restores the manager with refreshed categories after confirming deletion', async () => {
    await openDelete();
    mocks.getCategories.mockResolvedValue([]);
    await act(async () =>
        Array.from(container.querySelectorAll('button'))
            .find((button) => button.textContent === 'categories.delete')!
            .click(),
    );
    expect(mocks.deleteCategory).toHaveBeenCalledWith('folder');
    expect(mocks.changed).toHaveBeenCalledOnce();
    expect(container.querySelector('[aria-label="Category Manager"]')).not.toBeNull();
    expect(container.textContent).not.toContain('Small');
});

const draft = { name: 'Unsaved Name', color: '#123456', priority: '7', description: 'Draft description' };
const fillDraft = async () => {
    for (const [name, value] of Object.entries(draft)) {
        await act(async () => {
            const input = container.querySelector<HTMLInputElement>(`input[name="${name}"]`)!;
            Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, value);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
};
it.each(['create', 'edit'])('preserves an unsaved %s draft when deletion is canceled', async (mode) => {
    if (mode === 'edit') {
        await act(async () =>
            container.querySelector<HTMLButtonElement>('[aria-label="categories.edit: Small"]')!.click(),
        );
    }
    await fillDraft();
    await openDelete();
    await act(async () =>
        Array.from(container.querySelectorAll('button'))
            .find((button) => button.textContent === 'cancel')!
            .click(),
    );
    for (const [name, value] of Object.entries(draft)) {
        expect(container.querySelector<HTMLInputElement>(`input[name="${name}"]`)!.value).toBe(value);
    }
    await act(async () => container.querySelector<HTMLButtonElement>('button[type="submit"]')!.click());
    const values = { ...draft, priority: 7 };
    if (mode === 'edit') expect(mocks.updateCategory).toHaveBeenCalledWith('folder', values);
    else expect(mocks.createCategory).toHaveBeenCalledWith(values);
});
it('clears a creation draft when the manager is closed and reopened', async () => {
    await fillDraft();
    await act(async () =>
        root.render(
            <CategoryManagerModal visible={false} onDismissed={mocks.dismissed} onCategoryChanged={mocks.changed} />,
        ),
    );
    await act(async () =>
        root.render(<CategoryManagerModal visible onDismissed={mocks.dismissed} onCategoryChanged={mocks.changed} />),
    );
    expect(container.querySelector<HTMLInputElement>('input[name="name"]')!.value).toBe('');
});
