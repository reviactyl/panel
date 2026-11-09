import { act, type ComponentType, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Subuser } from '@/state/server/subusers';
import EditSubuserModal from './EditSubuserModal';

const mocks = vi.hoisted(() => ({
    save: vi.fn(),
    append: vi.fn(),
    clearFlashes: vi.fn(),
    addError: vi.fn(),
    rootAdmin: true,
    permissions: ['*'],
}));
vi.mock('@/api/server/users/createOrUpdateSubuser', () => ({ default: mocks.save }));
vi.mock('@/hoc/asModal', () => ({ default: () => (component: ComponentType) => component }));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [true] }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('easy-peasy', () => ({
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({
            user: { data: { rootAdmin: mocks.rootAdmin } },
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
            selector({ server: { data: { uuid: 'server-uuid' }, permissions: mocks.permissions } }),
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
    mocks.rootAdmin = true;
    mocks.permissions = ['*'];
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

const groupCheckbox = (title: string) => {
    const heading = Array.from(container.querySelectorAll('p')).find((element) => element.textContent === title)!;
    return heading.parentElement!.querySelector<HTMLInputElement>('input[type="checkbox"]')!;
};
const permissionCheckbox = (permission: string) =>
    container.querySelector<HTMLInputElement>(`[id="permission_${permission}"]`)!;

const renderInvitation = async () => {
    await act(async () => root.render(<EditSubuserModal visible />));
    await act(async () => {
        const email = container.querySelector<HTMLInputElement>('input[name="email"]')!;
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(email, subuser.email);
        email.dispatchEvent(new Event('input', { bubbles: true }));
    });
};

describe('subuser permission groups', () => {
    it('selects and clears only assignable permissions for a restricted inviter', async () => {
        mocks.rootAdmin = false;
        mocks.permissions = ['user.create', 'user.read', 'control.start'];
        await renderInvitation();
        await act(async () => permissionCheckbox('user.read').click());
        await act(async () => groupCheckbox('control').click());
        expect(groupCheckbox('control').checked).toBe(true);
        expect(permissionCheckbox('control.start').checked).toBe(true);
        for (const permission of ['control.console', 'control.stop', 'control.restart']) {
            expect(permissionCheckbox(permission).disabled).toBe(true);
            expect(permissionCheckbox(permission).checked).toBe(false);
        }
        await submit();
        expect(mocks.save).toHaveBeenLastCalledWith(
            'server-uuid',
            { email: subuser.email, permissions: ['user.read', 'control.start'] },
            undefined,
        );
        await act(async () => groupCheckbox('control').click());
        expect(groupCheckbox('control').checked).toBe(false);
        expect(permissionCheckbox('control.start').checked).toBe(false);
        await submit();
        expect(mocks.save).toHaveBeenLastCalledWith(
            'server-uuid',
            { email: subuser.email, permissions: ['user.read'] },
            undefined,
        );
    });

    it('disables a group with no assignable permissions', async () => {
        mocks.rootAdmin = false;
        mocks.permissions = ['user.create'];
        await renderInvitation();
        expect(groupCheckbox('control').disabled).toBe(true);
        expect(groupCheckbox('control').checked).toBe(false);
        await act(async () => groupCheckbox('control').click());
        await submit();
        expect(mocks.save).toHaveBeenLastCalledWith(
            'server-uuid',
            { email: subuser.email, permissions: [] },
            undefined,
        );
    });

    it.each(['owner', 'administrator'])('selects the full control group for an %s', async (role) => {
        mocks.rootAdmin = role === 'administrator';
        mocks.permissions = role === 'owner' ? ['*'] : [];
        await renderInvitation();
        await act(async () => groupCheckbox('control').click());
        await submit();
        expect(mocks.save).toHaveBeenLastCalledWith(
            'server-uuid',
            {
                email: subuser.email,
                permissions: ['control.console', 'control.start', 'control.stop', 'control.restart'],
            },
            undefined,
        );
    });
});
