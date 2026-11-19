import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createMemoryRouter, RouterProvider } from 'react-router-dom';
import { fireEvent, getByLabelText } from '@testing-library/dom';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import LoginCheckpointContainer from './LoginCheckpointContainer';
import ResetPasswordContainer from './ResetPasswordContainer';

const mocks = vi.hoisted(() => ({ checkpoint: vi.fn(), reset: vi.fn(), complete: vi.fn(), error: vi.fn() }));
vi.mock('@/api/auth/loginCheckpoint', () => ({ default: mocks.checkpoint }));
vi.mock('@/api/auth/performPasswordReset', () => ({ default: mocks.reset }));
vi.mock('@/api/auth/completeLogin', () => ({ default: mocks.complete }));
vi.mock('@/api/http', () => ({
    withHttpSessionTransition: (callback: () => Promise<unknown>) => callback(),
    httpErrorToHuman: (error: Error) => error.message,
}));
vi.mock('@/plugins/useFlash', () => ({ default: () => ({ clearAndAddHttpError: mocks.error }) }));
vi.mock('easy-peasy', () => ({ useStoreActions: () => ({ clearFlashes: vi.fn(), addFlash: mocks.error }) }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/auth/LoginFormContainer', async () => {
    const { Form } = await import('formik');
    return { default: ({ children }: { children: ReactNode }) => <Form>{children}</Form> };
});

let container: HTMLDivElement;
let root: Root;
let router: ReturnType<typeof createMemoryRouter>;
const render = async (path: string, state?: { token?: string }) => {
    window.history.replaceState(null, '', path);
    router = createMemoryRouter(
        [
            { path: '/auth/login', element: <p>Sign in</p> },
            { path: '/auth/login/checkpoint', element: <LoginCheckpointContainer /> },
            { path: '/auth/password/reset/:token', element: <ResetPasswordContainer /> },
        ],
        {
            initialEntries: [
                { pathname: path.split('?')[0], search: path.includes('?') ? `?${path.split('?')[1]}` : '', state },
            ],
        },
    );
    await act(async () => root.render(<RouterProvider router={router} />));
};
const fill = async (name: string, value: string) => {
    await act(async () => fireEvent.input(container.querySelector(`input[name=${name}]`)!, { target: { value } }));
};
const submit = async () => {
    await act(async () => fireEvent.submit(container.querySelector('form')!));
};
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.resetAllMocks();
    vi.spyOn(console, 'error').mockImplementation(() => {});
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    router.dispose();
    container.remove();
    vi.restoreAllMocks();
});

it.each([undefined, {}, { token: '' }])('replaces checkpoint without a challenge (%j) with sign-in', async (state) => {
    await render('/auth/login/checkpoint', state);
    expect(router.state.location.pathname).toBe('/auth/login');
    expect(router.state.historyAction).toBe('REPLACE');
    expect(container.textContent).toBe('Sign in');
    expect(mocks.checkpoint).not.toHaveBeenCalled();
    expect(console.error).not.toHaveBeenCalled();
});

it('labels both verification methods, clears switched codes, and submits the challenge token', async () => {
    mocks.checkpoint.mockResolvedValue({ complete: true });
    await render('/auth/login/checkpoint', { token: 'challenge-token' });
    const code = getByLabelText(container, 'checkpoint.auth-code') as HTMLInputElement;
    expect(code.labels).toHaveLength(1);
    await fill('code', '123456');
    await submit();
    expect(mocks.checkpoint).toHaveBeenLastCalledWith('challenge-token', '123456', '');
    expect(mocks.complete).toHaveBeenCalledOnce();
    await act(async () => container.querySelector<HTMLButtonElement>('button[type=button]')!.click());
    expect((getByLabelText(container, 'checkpoint.recovery-code') as HTMLInputElement).value).toBe('');
    await fill('recoveryCode', 'recovery-one');
    await submit();
    expect(mocks.checkpoint).toHaveBeenLastCalledWith('challenge-token', '', 'recovery-one');
    await act(async () => container.querySelector<HTMLButtonElement>('button[type=button]')!.click());
    expect((getByLabelText(container, 'checkpoint.auth-code') as HTMLInputElement).value).toBe('');
});

it('keeps the challenge usable after a rejected code', async () => {
    const error = new Error('Invalid code');
    mocks.checkpoint.mockRejectedValueOnce(error).mockResolvedValueOnce({ complete: true });
    await render('/auth/login/checkpoint', { token: 'challenge-token' });
    await fill('code', '000000');
    await submit();
    expect(mocks.error).toHaveBeenCalledWith({ error });
    expect(container.querySelector<HTMLButtonElement>('button[type=submit]')!.disabled).toBe(false);
    await fill('code', '123456');
    await submit();
    expect(mocks.complete).toHaveBeenCalledOnce();
});

it.each(['', '?email='])('accepts and submits an email when reset URL has no value (%s)', async (query) => {
    mocks.reset.mockRejectedValue(new Error('Invalid token'));
    await render(`/auth/password/reset/reset-token${query}`);
    const email = container.querySelector<HTMLInputElement>('input')!;
    expect(email.disabled).toBe(false);
    expect(getByLabelText(container, 'reset-password.email-label')).toBe(email);
    await act(async () => fireEvent.input(email, { target: { value: 'typed@example.com' } }));
    await fill('password', 'ValidPassword123!');
    await fill('passwordConfirmation', 'ValidPassword123!');
    await submit();
    expect(mocks.reset).toHaveBeenCalledExactlyOnceWith('typed@example.com', {
        token: 'reset-token',
        password: 'ValidPassword123!',
        passwordConfirmation: 'ValidPassword123!',
    });
    expect(mocks.error).toHaveBeenCalledWith({ type: 'error', title: 'Error', message: 'Invalid token' });
    expect(email.value).toBe('typed@example.com');
    expect(email.disabled).toBe(false);
});

it('retains and submits the URL email for existing reset links', async () => {
    mocks.reset.mockRejectedValue(new Error('Invalid token'));
    await render('/auth/password/reset/reset-token?email=demo%2Breset%40example.com');
    const email = container.querySelector<HTMLInputElement>('input')!;
    expect(email.value).toBe('demo+reset@example.com');
    expect(email.disabled).toBe(true);
    await fill('password', 'ValidPassword123!');
    await fill('passwordConfirmation', 'ValidPassword123!');
    await submit();
    expect(mocks.reset).toHaveBeenCalledWith(
        'demo+reset@example.com',
        expect.objectContaining({ token: 'reset-token' }),
    );
});
