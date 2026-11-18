import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { getByRole } from '@testing-library/dom';
import type { ServerCategory } from '@/api/account/getServerCategories';
import FolderView from './FolderView';

vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

it.each(['Games', 'Long folder name for production Minecraft servers and shared development environments'])(
    'keeps compact actions consistent and independent from opening %s',
    async (name) => {
        const folder: ServerCategory = {
            uuid: 'folder',
            name,
            description: 'Description beside the folder actions',
            color: '#123456',
            priority: 0,
            createdAt: new Date(),
            updatedAt: new Date(),
        };
        const onSelect = vi.fn();
        const onEditFolder = vi.fn();
        const onMoveFolder = vi.fn();
        const onDeleteFolder = vi.fn();
        const onManage = vi.fn();
        await act(async () =>
            root.render(
                <FolderView
                    categories={[folder]}
                    selected='all'
                    onSelect={onSelect}
                    onRetry={vi.fn()}
                    onManage={onManage}
                    onEditFolder={onEditFolder}
                    onMoveFolder={onMoveFolder}
                    onDeleteFolder={onDeleteFolder}
                />,
            ),
        );
        for (const [label, callback] of [
            ['categories.edit', onEditFolder],
            ['folders.move-folder', onMoveFolder],
            ['categories.delete', onDeleteFolder],
        ] as const) {
            const button = getByRole(container, 'button', { name: `${label}: ${name}` });
            expect(button.classList.contains('px-2')).toBe(true);
            expect(button.classList.contains('py-1')).toBe(true);
            expect(button.classList.contains('text-xs')).toBe(true);
            await act(async () => button.click());
            expect(callback).toHaveBeenCalledExactlyOnceWith(folder);
            expect(onSelect).not.toHaveBeenCalled();
        }
        await act(async () => getByRole(container, 'button', { name: `${name} ${folder.description}` }).click());
        expect(onSelect).toHaveBeenCalledExactlyOnceWith(folder.uuid);
        await act(async () => getByRole(container, 'button', { name: 'folders.home' }).click());
        expect(onSelect).toHaveBeenLastCalledWith('all');
        await act(async () => getByRole(container, 'button', { name: 'folders.create' }).click());
        expect(onManage).toHaveBeenCalledOnce();
    },
);
