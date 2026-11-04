import { act, type ComponentType, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Subuser } from '@/state/server/subusers';
import EditSubuserModal from './EditSubuserModal';

const mocks = vi.hoisted(() => ({ save: vi.fn(), append: vi.fn(), clearFlashes: vi.fn(), addError: vi.fn() }));
vi.mock('@/api/server/users/createOrUpdateSubuser', () => ({ default: mocks.save }));
vi.mock('@/hoc/asModal', () => ({ default: () => (component: ComponentType) => component }));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [true] }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('easy-peasy', () => ({
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({
            user: { data: { rootAdmin: true } },
            permissions: {
                data: {
                    control: { keys: { console: '', start: '', stop: '', restart: '' } },
                    file: { keys: { read: '', update: '', delete: '' } },
                    user: { keys: { read: '', create: '', update: '', delete: '' } },
                },
            },
        }),
    useStoreActions: (selector: (actions: unknown) => unknown) =>
        selector({ flashes: { clearFlashes: mocks.clearFlashes, clearAndAddHttpError: mocks.addError } }),
}));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'server-uuid' }, permissions: ['*'] } }),
        useStoreActions: (selector: (actions: unknown) => unknown) =>
            selector({ subusers: { appendSubuser: mocks.append } }),
    },
}));

const subuser = {
    uuid: 'subuser-uuid',
    email: 'viewer@example.com',
    permissions: ['control.console', 'file.update', 'user.delete'],
} as Subuser;
let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.save.mockResolvedValue(subuser);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

const selectPreset = async (preset: string) => {
    await act(async () => {
        const select = container.querySelector('select')!;
        select.value = preset;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });
};
const submit = async () => {
    await act(async () => {
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });
};

describe('subuser role presets', () => {
    it.each(['invite', 'edit'])('saves only observation permissions for a Viewer on %s', async (mode) => {
        const existing = mode === 'edit' ? subuser : undefined;
        await act(async () => root.render(<EditSubuserModal visible subuser={existing} />));
        if (!existing) {
            await act(async () => {
                const email = container.querySelector<HTMLInputElement>('input[name="email"]')!;
                Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(email, subuser.email);
                email.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }
        await selectPreset('viewer');
        expect(container.querySelector<HTMLInputElement>('#permission_control\\.console')!.checked).toBe(false);
        await submit();
        expect(mocks.save).toHaveBeenCalledWith(
            'server-uuid',
            { email: subuser.email, permissions: ['file.read', 'user.read'] },
            existing,
        );
        expect(mocks.append).toHaveBeenCalledWith(subuser);
    });

    it.each(['power', 'moderator'])('retains command and power access for the %s preset', async (preset) => {
        await act(async () => root.render(<EditSubuserModal visible subuser={subuser} />));
        await selectPreset(preset);
        await submit();
        expect(mocks.save).toHaveBeenCalledWith(
            'server-uuid',
            expect.objectContaining({
                permissions: expect.arrayContaining([
                    'control.console',
                    'control.start',
                    'control.stop',
                    'control.restart',
                ]),
            }),
            subuser,
        );
    });
});
