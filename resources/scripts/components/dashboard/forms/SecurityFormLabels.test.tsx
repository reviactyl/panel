import { act, type ComponentProps, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { getAllByLabelText, getByLabelText, getByRole } from '@testing-library/dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { CreatePasskeysForm, ListPasskeysForm } from './PasskeysForm';
import SetupTOTPDialog from './SetupTOTPDialog';

const mocks = vi.hoisted(() => ({
    register: vi.fn(),
    remove: vi.fn(),
    enable: vi.fn(),
    mutate: vi.fn(),
    updateUser: vi.fn(),
    error: vi.fn(),
    clear: vi.fn(),
}));

vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('swr', () => ({
    default: () => ({
        data: [{ id: 'passkey-id', name: 'Laptop', createdAt: new Date(), updatedAt: new Date() }],
        mutate: mocks.mutate,
    }),
}));
vi.mock('@/api/account/passkeys', () => ({
    getAccountPasskeys: vi.fn(),
    registerAccountPasskey: mocks.register,
    deleteAccountPasskey: mocks.remove,
}));
vi.mock('@/api/account/getTwoFactorTokenData', () => ({
    default: () => Promise.resolve({ image_url_data: 'otpauth://test', secret: 'TESTSECRET' }),
}));
vi.mock('@/api/account/enableAccountTwoFactor', () => ({ default: mocks.enable }));
vi.mock('@/plugins/useFlash', () => ({
    useFlashKey: () => ({ clearAndAddHttpError: mocks.error, clearFlashes: mocks.clear }),
}));
vi.mock('easy-peasy', () => ({ useStoreActions: () => mocks.updateUser }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
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
    mocks.enable.mockResolvedValue(['recovery-token']);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

const field = (scope: HTMLElement, label: string) => {
    const input = getByLabelText(scope, label) as HTMLInputElement;
    expect(input.id).not.toBe('');
    expect(input.labels?.length).toBe(1);
    expect(input.labels![0]!.htmlFor).toBe(input.id);
    expect(input.labels![0]!.control).toBe(input);
    return input;
};

const fill = async (input: HTMLInputElement, value: string) => {
    await act(async () => {
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, value);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
};

describe('security form label associations', () => {
    it('keeps labels distinct across form instances and stable on rerender', async () => {
        const forms = (
            <>
                <CreatePasskeysForm />
                <CreatePasskeysForm />
            </>
        );
        await act(async () => root.render(forms));
        const inputs = ['passkeys.name', 'passkeys.password'].flatMap(
            (label) => getAllByLabelText(container, label) as HTMLInputElement[],
        );
        const ids = inputs.map((input) => input.id);
        expect(ids.every(Boolean)).toBe(true);
        expect(new Set(ids).size).toBe(4);
        await fill(inputs[0]!, 'Laptop');
        expect(inputs.map((input) => input.id)).toEqual(ids);
        expect(inputs.every((input) => input.labels![0]!.control === input)).toBe(true);
    });

    it('labels passkey creation fields uniquely and preserves validation and submission', async () => {
        await act(async () => root.render(<CreatePasskeysForm />));
        const name = field(container, 'passkeys.name');
        const password = field(container, 'passkeys.password');
        expect(name.id).not.toBe(password.id);
        expect(name.maxLength).toBe(191);
        expect(password.type).toBe('password');
        expect(password.required).toBe(true);
        const submit = getByRole(container, 'button', { name: 'passkeys.register' }) as HTMLButtonElement;
        expect(submit.disabled).toBe(true);
        await fill(name, 'Work laptop');
        await fill(password, 'current-password');
        expect(submit.disabled).toBe(false);
        expect(field(container, 'passkeys.name').id).toBe(name.id);
        await act(async () =>
            container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
        );
        expect(mocks.register).toHaveBeenCalledWith('current-password', 'Work laptop');
        expect(name.value).toBe('');
        expect(password.value).toBe('');
    });

    it('labels the passkey removal confirmation password and preserves autofill and submission', async () => {
        await act(async () => root.render(<ListPasskeysForm />));
        await act(async () => container.querySelector('button')!.click());
        const dialog = getByRole(document.body, 'dialog');
        const password = field(dialog, 'passkeys.password');
        expect(password.type).toBe('password');
        expect(password.autocomplete).toBe('current-password');
        await fill(password, 'current-password');
        await act(async () => getByRole(dialog, 'button', { name: 'passkeys.remove' }).click());
        expect(mocks.remove).toHaveBeenCalledWith('passkey-id', 'current-password');
    });

    it('labels the TOTP code and password and preserves validation, autofill, and submission', async () => {
        const onTokens = vi.fn();
        await act(async () => root.render(<SetupTOTPDialog open onClose={vi.fn()} onTokens={onTokens} />));
        const dialog = getByRole(document.body, 'dialog');
        const code = field(dialog, '2fa.setup.scan');
        const password = field(dialog, '2fa.setup.password');
        expect(code.id).not.toBe(password.id);
        expect(code.inputMode).toBe('numeric');
        expect(code.autocomplete).toBe('one-time-code');
        expect(code.pattern).toBe('\\d{6}');
        expect(password.type).toBe('password');
        const submit = getByRole(dialog, 'button', { name: '2fa.setup.enable' }) as HTMLButtonElement;
        expect(submit.disabled).toBe(true);
        await fill(code, '12345');
        await fill(password, 'current-password');
        expect(submit.disabled).toBe(true);
        await fill(code, '123456');
        expect(submit.disabled).toBe(false);
        await act(async () =>
            dialog.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
        );
        expect(mocks.enable).toHaveBeenCalledWith('123456', 'current-password');
        expect(onTokens).toHaveBeenCalledWith(['recovery-token']);
    });
});
