import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { rawDataToFileObject } from '@/api/transformers';
import type { FileObject } from '@/api/server/files/loadDirectory';
import FileDropdownMenu, { Row } from './FileDropdownMenu';
import { FaTrash } from 'react-icons/fa6';
import FileObjectRow from './FileObjectRow';

const mocks = vi.hoisted(() => ({ download: vi.fn(), running: false }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({
                server: { data: { uuid: 'uuid', id: 'test' } },
                files: {
                    directory: '/data',
                    selectedFiles: [],
                    massActionFiles: mocks.running ? ['/data/test.txt'] : [],
                },
            }),
        useStoreActions: () => vi.fn(),
    },
}));
vi.mock('easy-peasy', async (original) => ({
    ...(await original<typeof import('easy-peasy')>()),
    useStoreState: () => 'tar.gz',
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ mutate: vi.fn() }) }));
vi.mock('@/plugins/useFlash', () => ({ default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }) }));
vi.mock('@/api/server/files/getFileDownloadUrl', () => ({ default: mocks.download }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [true] }));
vi.mock('./SelectFileCheckbox', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Fade', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/transitions/FadeTransition', async () => {
    const { useEffect, useRef } = await import('react');
    return {
        default: ({ show, children, afterLeave }: { show: boolean; children: ReactNode; afterLeave?: () => void }) => {
            const wasVisible = useRef(show);
            useEffect(() => {
                const didLeave = wasVisible.current && !show;
                wasVisible.current = show;
                if (didLeave) afterLeave?.();
            }, [show, afterLeave]);
            return show ? children : null;
        },
    };
});
vi.mock('@/api/server/files/loadDirectory', () => ({ default: () => new Promise(() => {}) }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/dialog', () => ({ Dialog: { Confirm: () => null } }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('./ChmodFileModal', () => ({ default: () => null }));
vi.mock('./RenameFileModal', () => ({
    default: ({ onDismissed }: { onDismissed: () => void }) => (
        <input aria-label='rename' autoFocus onKeyDown={(e) => e.key === 'Escape' && onDismissed()} />
    ),
}));

let container: HTMLDivElement;
let root: Root;
let portal: HTMLDivElement;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    mocks.running = false;
    mocks.download.mockReset().mockRejectedValue(new Error('Download unavailable'));
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
    vi.useRealTimers();
});
const render = async () => {
    const file = { name: 'test.txt', key: 'test', isFile: true, isArchiveType: () => false } as FileObject;
    await act(async () => root.render(<FileDropdownMenu file={file} />));
};
const toggle = () => container.querySelector('svg')!.parentElement as HTMLButtonElement;
const row = (title: string) =>
    Array.from(container.querySelectorAll('button')).find((button) => button.textContent === `dropdown.${title}`)!;
const click = async (button: HTMLButtonElement) => {
    await act(async () => button.click());
    await act(async () => vi.runOnlyPendingTimers());
};
const escape = async (target: Element) => {
    await act(async () => target.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true })));
};

describe('file action controls', () => {
    it('marks file deletion as danger without changing normal actions or archive styles', async () => {
        await render();
        await click(toggle());
        expect(row('delete').classList.contains('text-danger-text')).toBe(true);
        expect(row('rename').classList.contains('text-danger-text')).toBe(false);
        for (const title of ['rename', 'delete', 'archive']) {
            expect(row(title).className).toContain('hover:bg-gray-700');
            expect(row(title).classList.contains('focus-visible:bg-gray-700')).toBe(true);
            expect(row(title).classList.contains('focus-visible:outline-2')).toBe(true);
        }
        const formats = container.querySelectorAll<HTMLButtonElement>('button[aria-label="archive-as"]');
        expect(Array.from(formats, (button) => button.textContent)).toEqual(['tar.gz', 'zip']);
        for (const button of formats) {
            expect(button.className).toContain('hover:bg-gray-700');
            expect(button.classList.contains('focus-visible:bg-gray-700')).toBe(true);
        }
    });

    it('keeps shared destructive recycle rows marked and disabled rows inert', async () => {
        const onClick = vi.fn();
        await act(async () =>
            root.render(<Row icon={FaTrash} title='Delete permanently' danger disabled onClick={onClick} />),
        );
        const button = container.querySelector('button')!;
        expect(button.classList.contains('text-danger-text')).toBe(true);
        expect(button.disabled).toBe(true);
        expect(button.className).not.toContain('hover:bg-');
        await click(button);
        expect(onClick).not.toHaveBeenCalled();
    });

    it('exposes native focusable controls and positions keyboard clicks at the toggle', async () => {
        await render();
        expect(toggle().tagName).toBe('BUTTON');
        expect(toggle().tabIndex).toBe(0);
        vi.spyOn(toggle(), 'getBoundingClientRect').mockReturnValue({ right: 600 } as DOMRect);
        await click(toggle());
        expect(toggle().getAttribute('aria-expanded')).toBe('true');
        expect(document.getElementById(toggle().getAttribute('aria-controls')!)!.style.left).toBe('600px');
        for (const title of ['rename', 'move', 'permissions', 'copy', 'archive', 'download', 'delete']) {
            expect(row(title).tagName).toBe('BUTTON');
            expect(row(title).tabIndex).toBe(0);
            expect(row(title).disabled).toBe(false);
        }
    });

    it('opens Rename without stealing its focus and restores the trigger after dismissal', async () => {
        await render();
        await click(toggle());
        await click(row('rename'));
        const input = container.querySelector('input')!;
        expect(document.activeElement).toBe(input);
        expect(toggle().getAttribute('aria-expanded')).toBe('false');
        await escape(input);
        expect(container.querySelector('input')).toBeNull();
        expect(document.activeElement).toBe(toggle());
    });

    it('activates Download and returns focus to the trigger', async () => {
        await render();
        await click(toggle());
        await click(row('download'));
        expect(mocks.download).toHaveBeenCalledExactlyOnceWith('uuid', '/data/test.txt');
        expect(toggle().getAttribute('aria-expanded')).toBe('false');
        expect(document.activeElement).toBe(toggle());
    });

    it('moves focus into Move while entries load and restores it after dismissal', async () => {
        await render();
        await click(toggle());
        await click(row('move'));
        const heading = document.querySelector('[role=dialog] h2')!;
        expect(heading).not.toBeNull();
        expect(document.activeElement).toBe(heading);
        expect(toggle().getAttribute('aria-expanded')).toBe('false');
        expect(document.querySelector('[role=dialog]')!.textContent).toContain('move-modal.loading');
        await escape(heading);
        expect(document.querySelector('[role=dialog] h2')).toBeNull();
        expect(document.activeElement).toBe(toggle());
    });

    it('closes on Escape and preserves focus when tabbing away', async () => {
        await render();
        await click(toggle());
        row('download').focus();
        await escape(row('download'));
        expect(document.activeElement).toBe(toggle());
        expect(toggle().getAttribute('aria-expanded')).toBe('false');
        expect(document.getElementById(toggle().getAttribute('aria-controls')!)!.inert).toBe(true);
        await click(toggle());
        const outside = document.createElement('button');
        document.body.append(outside);
        await act(async () => outside.focus());
        expect(toggle().getAttribute('aria-expanded')).toBe('false');
        expect(document.activeElement).toBe(outside);
        outside.remove();
    });

    it('keeps bulk actions disabled while they are running', async () => {
        mocks.running = true;
        await render();
        await click(toggle());
        for (const title of ['move', 'archive', 'delete']) expect(row(title).disabled).toBe(true);
    });

    it('keeps archive visibility and expanded state in sync when pointer and focus overlap', async () => {
        await render();
        await click(toggle());
        const archive = row('archive');
        const formats = document.getElementById(archive.getAttribute('aria-controls')!)!;
        const hover = async (type: 'mouseover' | 'mouseout') => {
            await act(async () =>
                archive.dispatchEvent(
                    new MouseEvent(type, {
                        bubbles: true,
                        relatedTarget: type === 'mouseover' ? null : row('download'),
                    }),
                ),
            );
            await act(async () => vi.runOnlyPendingTimers());
        };
        await act(async () => archive.focus());
        await hover('mouseover');
        await hover('mouseout');
        expect(archive.getAttribute('aria-expanded')).toBe('true');
        expect(formats.style.display).toBe('block');
        await act(async () => row('download').focus());
        expect(archive.getAttribute('aria-expanded')).toBe('false');
        expect(formats.style.display).toBe('none');
        await act(async () => archive.focus());
        await hover('mouseover');
        await act(async () => row('download').focus());
        expect(archive.getAttribute('aria-expanded')).toBe('true');
        expect(formats.style.display).toBe('block');
        await hover('mouseout');
        expect(archive.getAttribute('aria-expanded')).toBe('false');
        expect(formats.style.display).toBe('none');
    });
});

describe.each([false, true])('file context menus (grid=%s)', (grid) => {
    it('replaces the open menu on right-click and repositions repeated right-clicks', async () => {
        const files = ['first.txt', 'second.txt'].map((name) =>
            rawDataToFileObject({
                object: 'file_object',
                attributes: {
                    name,
                    is_file: true,
                    mimetype: 'text/plain',
                    size: 1,
                    created_at: '2026-11-01T00:00:00Z',
                    modified_at: '2026-11-01T00:00:00Z',
                },
            }),
        );
        await act(async () =>
            root.render(
                <MemoryRouter>
                    {files.map((file) => (
                        <FileObjectRow key={file.key} file={file} grid={grid} />
                    ))}
                </MemoryRouter>,
            ),
        );
        const toggles = Array.from(container.querySelectorAll<HTMLButtonElement>('button[aria-expanded]')).filter(
            (button) => button.hasAttribute('aria-label'),
        );
        const links = container.querySelectorAll('a');
        const rightClick = async (index: number, clientX: number) => {
            await act(async () =>
                links[index]!.dispatchEvent(
                    new MouseEvent('contextmenu', { bubbles: true, cancelable: true, button: 2, clientX }),
                ),
            );
        };

        await rightClick(0, 500);
        expect(toggles[0]!.getAttribute('aria-expanded')).toBe('true');
        await rightClick(1, 600);
        expect(toggles[0]!.getAttribute('aria-expanded')).toBe('false');
        expect(toggles[1]!.getAttribute('aria-expanded')).toBe('true');
        await rightClick(1, 700);
        expect(toggles[1]!.getAttribute('aria-expanded')).toBe('true');
        expect(document.getElementById(toggles[1]!.getAttribute('aria-controls')!)!.style.left).toBe('700px');
        expect(container.querySelectorAll('button[aria-label][aria-expanded=true]')).toHaveLength(1);

        await act(async () => document.body.dispatchEvent(new MouseEvent('contextmenu', { bubbles: true, button: 2 })));
        expect(toggles[1]!.getAttribute('aria-expanded')).toBe('false');
    });
});
