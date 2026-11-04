import { act, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { SWRConfig } from 'swr';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { FileObject } from '@/api/server/files/loadDirectory';
import useFileManagerSwr, { getDirectorySwrKey } from '@/plugins/useFileManagerSwr';
import NewDirectoryButton from './NewDirectoryButton';

const mocks = vi.hoisted(() => ({ create: vi.fn(), load: vi.fn(), error: vi.fn() }));
vi.mock('@/api/server/files/createDirectory', () => ({ default: mocks.create }));
vi.mock('@/api/server/files/loadDirectory', () => ({ default: mocks.load }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'uuid' } }, files: { directory: '/' } }),
    },
}));
vi.mock('@/plugins/useFlash', () => ({ useFlashKey: () => ({ clearAndAddHttpError: mocks.error }) }));
vi.mock('react-i18next', () => {
    const t = (key: string) => key;
    return { useTranslation: () => ({ t }) };
});
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/components/button/index', () => {
    const Button = (props: ButtonHTMLAttributes<HTMLButtonElement>) => <button {...props} />;
    return { Button: Object.assign(Button, { Text: Button }) };
});
vi.mock('@/reviactyl/elements/dialog', async () => {
    const { createContext } = await import('react');
    const Dialog = ({ open, children }: { open: boolean; children: ReactNode }) =>
        open ? <div role='dialog'>{children}</div> : null;
    return {
        Dialog: Object.assign(Dialog, { Footer: ({ children }: { children: ReactNode }) => children }),
        DialogWrapperContext: createContext({}),
    };
});

const folder = (name: string) => ({ name, key: name, isFile: false }) as FileObject;
const Listing = () => {
    const { data } = useFileManagerSwr();
    return (
        <ul>
            {data?.map((file, index) => (
                <li key={index}>{file.name}</li>
            ))}
        </ul>
    );
};
let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.create.mockReset().mockResolvedValue(undefined);
    mocks.load.mockReset();
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});
const render = async (files: FileObject[]) => {
    await act(async () =>
        root.render(
            <SWRConfig value={{ provider: () => new Map([[getDirectorySwrKey('uuid', '/'), { data: files }]]) }}>
                <Listing />
                <NewDirectoryButton />
            </SWRConfig>,
        ),
    );
    await act(async () => container.querySelector<HTMLButtonElement>('[aria-label="create-directory"]')!.click());
};
const submit = async (name: string) => {
    await act(async () => {
        const input = container.querySelector<HTMLInputElement>('input[name="directoryName"]')!;
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, name);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await act(async () =>
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
    );
};
const names = () => Array.from(container.querySelectorAll('li')).map((entry) => entry.textContent);

describe('directory creation listing', () => {
    it.each([
        ['plugins/example', ['plugins'], ['plugins']],
        ['new-folder', ['plugins'], ['plugins', 'new-folder']],
        ['new-parent/child', ['plugins'], ['plugins', 'new-parent']],
        ['plugins/../other/child', ['plugins'], ['plugins', 'other']],
    ])('refreshes actual direct children after creating %s', async (name, initial, actual) => {
        mocks.load.mockResolvedValue(actual.map(folder));
        await render(initial.map(folder));
        await submit(name);
        expect(mocks.create).toHaveBeenCalledWith('uuid', '/', name);
        expect(names()).toEqual(actual);
        expect(mocks.load).toHaveBeenCalledWith('uuid', '/');
        expect(container.querySelector('[role="dialog"]')).toBeNull();
    });

    it('waits for creation before refreshing and for the listing before closing', async () => {
        let created!: () => void;
        let loaded!: (files: FileObject[]) => void;
        mocks.create.mockReturnValue(new Promise<void>((resolve) => (created = resolve)));
        mocks.load.mockReturnValue(new Promise<FileObject[]>((resolve) => (loaded = resolve)));
        await render([folder('plugins')]);
        await submit('plugins/example');
        expect(mocks.load).not.toHaveBeenCalled();
        await act(async () => created());
        expect(container.querySelector('[role="dialog"]')).not.toBeNull();
        expect(names()).toEqual(['plugins']);
        await act(async () => loaded([folder('plugins')]));
        expect(container.querySelector('[role="dialog"]')).toBeNull();
        expect(names()).toEqual(['plugins']);
    });

    it('retains the listing and reports a failed creation', async () => {
        const error = new Error('Creation failed');
        mocks.create.mockRejectedValue(error);
        await render([folder('plugins')]);
        await submit('plugins/example');
        expect(names()).toEqual(['plugins']);
        expect(mocks.load).not.toHaveBeenCalled();
        expect(mocks.error).toHaveBeenCalledWith(error);
        expect(container.querySelector('[role="dialog"]')).not.toBeNull();
    });
});
