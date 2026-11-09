import { act, type ComponentProps, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { getByRole, queryByRole } from '@testing-library/dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import AccountApiContainer from './AccountApiContainer';
import DeleteSSHKeyButton from './ssh/DeleteSSHKeyButton';
import { ListPasskeysForm } from './forms/PasskeysForm';

const mocks = vi.hoisted(() => ({
    name: 'Work laptop',
    deleteApi: vi.fn(),
    deleteSSH: vi.fn(),
    deletePasskey: vi.fn(),
    mutate: vi.fn(),
}));

vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/api/account/getApiKeys', () => ({
    default: async () => [
        { identifier: 'api-identifier', description: mocks.name, allowedIps: [], lastUsedAt: null },
        { identifier: 'other-api', description: 'Other API key', allowedIps: [], lastUsedAt: null },
    ],
}));
vi.mock('@/api/account/deleteApiKey', () => ({ default: mocks.deleteApi }));
vi.mock('@/api/account/ssh-keys', () => ({
    deleteSSHKey: mocks.deleteSSH,
    useSSHKeys: () => ({ mutate: mocks.mutate }),
}));
vi.mock('@/api/account/passkeys', () => ({
    getAccountPasskeys: vi.fn(),
    registerAccountPasskey: vi.fn(),
    deleteAccountPasskey: mocks.deletePasskey,
}));
vi.mock('swr', () => ({
    default: () => ({
        data: [
            { id: 'passkey-id', name: mocks.name, createdAt: new Date(), updatedAt: new Date() },
            { id: 'other-passkey', name: 'Other passkey', createdAt: new Date(), updatedAt: new Date() },
        ],
        mutate: mocks.mutate,
    }),
}));
vi.mock('@/plugins/useFlash', () => ({
    useFlashKey: () => ({ clearAndAddHttpError: vi.fn(), clearFlashes: vi.fn(), addError: vi.fn() }),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/components/dashboard/AccountContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/ContentBox', () => ({
    default: ({ children }: { children: ReactNode }) => <section>{children}</section>,
}));
vi.mock('@/components/dashboard/forms/CreateApiKeyForm', () => ({ default: () => null }));
vi.mock('framer-motion', async () => {
    const actual = await vi.importActual<typeof import('framer-motion')>('framer-motion');
    const { forwardRef } = await import('react');
    const Div = forwardRef<HTMLDivElement, ComponentProps<typeof actual.motion.div>>(
        (
            { initial: _initial, animate: _animate, exit: _exit, variants: _variants, onUpdate: _onUpdate, ...props },
            ref,
        ) => <div {...(props as ComponentProps<'div'>)} ref={ref} />,
    );
    return {
        ...actual,
        motion: { ...actual.motion, div: Div },
        AnimatePresence: ({ children }: { children: ReactNode }) => children,
    };
});

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.clearAllMocks();
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

describe.each(['Work laptop', ''])('credential delete actions with name "%s"', (name) => {
    beforeEach(() => {
        mocks.name = name;
    });

    it.each([
        ['API key', () => <AccountApiContainer />, 'api.delete', 'api-identifier'],
        [
            'SSH key',
            () => <DeleteSSHKeyButton name={name} fingerprint='SHA256:fingerprint' />,
            'ssh.delete.delete-ssh-key',
            'SHA256:fingerprint',
        ],
        ['passkey', () => <ListPasskeysForm />, 'passkeys.remove', 'passkey-id'],
    ] as const)('names the %s action and opens its selected-credential confirmation', async (_, render, action, id) => {
        await act(async () => root.render(render()));
        const credential = name || id;
        const button = getByRole(container, 'button', { name: `${action}: ${credential}` });
        expect(queryByRole(document.body, 'dialog')).toBeNull();
        await act(async () => button.click());
        const dialog = getByRole(document.body, 'dialog', { name: action });
        expect(dialog.querySelector('code')?.textContent).toBe(action === 'api.delete' ? id : credential);
        expect(mocks.deleteApi).not.toHaveBeenCalled();
        expect(mocks.deleteSSH).not.toHaveBeenCalled();
        expect(mocks.deletePasskey).not.toHaveBeenCalled();
        await act(async () => getByRole(dialog, 'button', { name: 'Cancel' }).click());
        expect(queryByRole(document.body, 'dialog')).toBeNull();
    });
});
