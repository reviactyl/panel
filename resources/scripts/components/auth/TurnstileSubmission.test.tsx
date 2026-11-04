import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { Form } from 'formik';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import type { ApplicationStore } from '@/state';
import LoginContainer from './LoginContainer';
import RegisterContainer from './RegisterContainer';
import ForgotPasswordContainer from './ForgotPasswordContainer';

const mocks = vi.hoisted(() => ({
    login: vi.fn(),
    register: vi.fn(),
    resetPassword: vi.fn(),
    clearFlashes: vi.fn(),
    clearAndAddHttpError: vi.fn(),
    addFlash: vi.fn(),
}));
vi.mock('@/api/auth/login', () => ({ default: mocks.login }));
vi.mock('@/api/auth/register', () => ({ default: mocks.register }));
vi.mock('@/api/auth/requestPasswordResetEmail', () => ({ default: mocks.resetPassword }));
vi.mock('@/plugins/useFlash', () => ({ default: () => mocks }));
vi.mock('easy-peasy', async (importOriginal) => ({
    ...(await importOriginal<typeof import('easy-peasy')>()),
    useStoreState: (selector: (state: ApplicationStore) => unknown) =>
        selector({
            settings: { data: { captcha: { provider: 'turnstile', turnstile: { siteKey: 'test-key' } } } },
        } as ApplicationStore),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('./LoginFormContainer', () => ({
    default: ({ children }: { children: ReactNode }) => <Form>{children}</Form>,
}));
vi.mock('./OAuthButtons', () => ({ default: () => null }));

let container: HTMLDivElement;
let root: Root;
let verify: (token: string) => void;
let reset: ReturnType<typeof vi.fn<(widgetId: string) => void>>;

const fill = async (values: Record<string, string>) => {
    for (const [name, value] of Object.entries(values)) {
        await act(async () => {
            const input = container.querySelector<HTMLInputElement>(`input[name="${name}"]`)!;
            Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, value);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
};
const submit = async () => {
    await act(async () => {
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });
};
const completeChallenge = async (token: string) => {
    await act(async () => verify(token));
};
const render = async (form: ReactNode) => {
    await act(async () => root.render(<MemoryRouter>{form}</MemoryRouter>));
};

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    vi.spyOn(console, 'error').mockImplementation(() => {});
    reset = vi.fn();
    window.turnstile = {
        render: vi.fn((_container, options) => {
            verify = options.callback;
            return 'widget-1';
        }),
        reset,
        remove: vi.fn(),
    };
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    delete window.turnstile;
    vi.restoreAllMocks();
});

it('renews Turnstile after a mistyped password and submits corrected credentials with a fresh token', async () => {
    mocks.login.mockRejectedValueOnce(new Error('Invalid password')).mockResolvedValueOnce({ complete: false });
    await render(<LoginContainer />);
    await fill({ username: 'demo', password: 'mistyped' });
    await completeChallenge('first-token');
    await submit();

    expect(reset).toHaveBeenCalledWith('widget-1');
    await fill({ password: 'corrected' });
    await submit();
    expect(mocks.login).toHaveBeenCalledTimes(1);
    await completeChallenge('second-token');
    await submit();

    expect(mocks.login).toHaveBeenCalledTimes(2);
    expect(mocks.login).toHaveBeenLastCalledWith({
        username: 'demo',
        password: 'corrected',
        captchaToken: 'second-token',
        captchaProvider: 'turnstile',
    });
});

it('renews Turnstile after rejected registration and sends the new token on retry', async () => {
    mocks.register.mockRejectedValue({
        response: {
            status: 422,
            data: {
                errors: [
                    {
                        detail: 'The username has already been taken.',
                        meta: { source_field: 'username' },
                    },
                ],
            },
        },
    });
    await render(<RegisterContainer />);
    await fill({
        email: 'demo@example.com',
        username: 'demo',
        firstName: 'Demo',
        lastName: 'User',
        password: 'password123',
        passwordConfirmation: 'password123',
    });
    await completeChallenge('first-token');
    await submit();

    expect(reset).toHaveBeenCalledWith('widget-1');
    expect(container.querySelector('input[name=username]')?.parentElement?.parentElement?.textContent).toContain(
        'The username has already been taken.',
    );
    await completeChallenge('second-token');
    await submit();

    expect(mocks.register).toHaveBeenCalledTimes(2);
    expect(mocks.register).toHaveBeenLastCalledWith(
        expect.objectContaining({ 'cf-turnstile-response': 'second-token' }),
    );
    expect(reset).toHaveBeenCalledTimes(2);
});

it.each(['success', 'failure'])(
    'renews Turnstile after password-reset %s and allows another submission',
    async (outcome) => {
        if (outcome === 'success') mocks.resetPassword.mockResolvedValue('Email sent');
        else mocks.resetPassword.mockRejectedValue(new Error('Request failed'));
        await render(<ForgotPasswordContainer />);
        await fill({ email: 'demo@example.com' });
        await completeChallenge('first-token');
        await submit();

        expect(reset).toHaveBeenCalledWith('widget-1');
        await fill({ email: 'another@example.com' });
        await submit();
        expect(mocks.resetPassword).toHaveBeenCalledTimes(1);
        await completeChallenge('second-token');
        await submit();

        expect(mocks.resetPassword).toHaveBeenCalledTimes(2);
        expect(mocks.resetPassword).toHaveBeenLastCalledWith('another@example.com', 'second-token', 'turnstile');
        expect(reset).toHaveBeenCalledTimes(2);
    },
);
