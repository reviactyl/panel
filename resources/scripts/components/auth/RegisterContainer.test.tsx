import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import RegisterContainer from './RegisterContainer';

const mocks = vi.hoisted(() => ({
    register: vi.fn(),
    provider: 'disable',
    missingWidget: false,
    execute: vi.fn(),
    reset: vi.fn(),
    captcha: {} as { onVerify: (token: string) => void; onError: () => void; onExpire: () => void },
    clearFlashes: vi.fn(),
    clearAndAddHttpError: vi.fn(),
    addError: vi.fn(),
}));

vi.mock('@/api/auth/register', () => ({ default: mocks.register }));
vi.mock('@/plugins/useFlash', () => ({ default: () => mocks }));
vi.mock('easy-peasy', () => ({
    useStoreState: () => ({ provider: mocks.provider, recaptcha: {}, turnstile: {} }),
}));
vi.mock('reaptcha', async () => {
    const { forwardRef, useImperativeHandle } = await import('react');
    return {
        default: forwardRef((props: typeof mocks.captcha, ref) => {
            mocks.captcha = props;
            useImperativeHandle(ref, () =>
                mocks.missingWidget ? null : { execute: mocks.execute, reset: mocks.reset },
            );
            return null;
        }),
    };
});
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('react-router-dom', () => ({ Link: ({ children }: { children: ReactNode }) => <a>{children}</a> }));
vi.mock('@/components/auth/LoginFormContainer', async () => {
    const { Form } = await import('formik');
    return { default: ({ children }: { children: ReactNode }) => <Form>{children}</Form> };
});

let container: HTMLDivElement;
let root: Root;

const values = {
    firstName: 'Demo',
    lastName: 'User',
    email: 'demo@example.com',
    username: 'demo',
    password: 'ValidPassword123!',
    passwordConfirmation: 'ValidPassword123!',
};

const validationError = (sourceField?: string, detail = 'This value is invalid.') => ({
    code: 'ValidationException',
    status: '422',
    detail,
    meta: { source_field: sourceField, rule: 'unique' },
});

const submit = async (errors: unknown, status = 422) => {
    const error = { message: 'Request failed.', response: { status, data: { errors } } };
    mocks.register.mockRejectedValueOnce(error);
    await act(async () => {
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });
    expect(mocks.register).toHaveBeenCalledTimes(1);
    expect(container.querySelector('button[type=submit]')?.hasAttribute('disabled')).toBe(false);
    return error;
};

const fieldMessage = (name: string) => container.querySelector(`input[name=${name}]`)!.parentElement!.parentElement;

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.resetAllMocks();
    mocks.provider = 'disable';
    mocks.missingWidget = false;
    mocks.execute.mockResolvedValue(undefined);
    mocks.reset.mockResolvedValue(undefined);
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    await act(async () => root.render(<RegisterContainer />));
    for (const [name, value] of Object.entries(values)) {
        await act(async () => {
            const input = container.querySelector<HTMLInputElement>(`input[name=${name}]`)!;
            Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, value);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

describe('registration server validation', () => {
    it.each([
        ['username', 'username'],
        ['email', 'email'],
        ['first_name', 'firstName'],
        ['name_first', 'firstName'],
        ['last_name', 'lastName'],
        ['name_last', 'lastName'],
        ['password', 'password'],
        ['password_confirmation', 'passwordConfirmation'],
    ])('shows the Panel %s error beside %s', async (source, field) => {
        const message = source === 'username' ? 'The username has already been taken.' : `Invalid ${source}.`;
        await submit([validationError(source, message)]);
        expect(fieldMessage(field)?.textContent).toContain(message);
        expect(mocks.addError).not.toHaveBeenCalled();
        expect(mocks.clearAndAddHttpError).not.toHaveBeenCalled();
    });

    it('shows multiple field errors together and keeps the first message for each field', async () => {
        await submit([
            validationError('username', 'The username has already been taken.'),
            validationError('username', 'Another username error.'),
            validationError('email', 'The email has already been taken.'),
        ]);
        expect(fieldMessage('username')?.textContent).toContain('The username has already been taken.');
        expect(fieldMessage('username')?.textContent).not.toContain('Another username error.');
        expect(fieldMessage('email')?.textContent).toContain('The email has already been taken.');
    });

    it.each(['g-recaptcha-response', 'constructor', undefined])(
        'shows an unmapped %s message globally alongside mapped errors',
        async (source) => {
            await submit([
                validationError('email', 'The email has already been taken.'),
                validationError(source, 'Please try again.'),
            ]);
            expect(fieldMessage('email')?.textContent).toContain('The email has already been taken.');
            expect(mocks.addError).toHaveBeenCalledExactlyOnceWith({ message: 'Please try again.' });
        },
    );

    it.each([
        { errors: [] },
        { errors: [null, {}] },
        { errors: { username: ['The username has already been taken.'] } },
    ])('falls back to the global HTTP error for unusable validation data $errors', async ({ errors }) => {
        const error = await submit(errors);
        expect(mocks.clearAndAddHttpError).toHaveBeenCalledExactlyOnceWith({ error });
    });

    it('keeps other HTTP failures visible globally', async () => {
        const error = await submit([validationError('username')], 500);
        expect(mocks.clearAndAddHttpError).toHaveBeenCalledExactlyOnceWith({ error });
    });

    it('lets the user correct a rejected value and submit again', async () => {
        await submit([validationError('username', 'The username has already been taken.')]);
        await act(async () => {
            const input = container.querySelector<HTMLInputElement>('input[name=username]')!;
            Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, 'new-user');
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
        mocks.register.mockResolvedValueOnce({ complete: false });
        await act(async () => {
            container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        });
        expect(mocks.register).toHaveBeenLastCalledWith(expect.objectContaining({ username: 'new-user' }));
        expect(mocks.register).toHaveBeenCalledTimes(2);
        expect(fieldMessage('username')?.textContent).not.toContain('The username has already been taken.');
    });
});

describe('invisible registration reCAPTCHA', () => {
    const enableCaptcha = async () => {
        mocks.provider = 'recaptcha';
        await act(async () => root.render(<RegisterContainer />));
    };
    const submitForm = async () => {
        await act(async () => {
            container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        });
    };
    const expectRecovery = () => {
        expect(container.querySelector('button[type=submit]')?.hasAttribute('disabled')).toBe(false);
        expect(container.querySelector('input[name=username]')?.hasAttribute('disabled')).toBe(false);
        expect(mocks.addError).toHaveBeenLastCalledWith({ message: 'captcha-failed' });
        expect(mocks.register).not.toHaveBeenCalled();
    };

    it('recovers from rejected execution and submits once after a successful retry', async () => {
        await enableCaptcha();
        mocks.execute.mockRejectedValueOnce(new Error('Widget is not ready.'));
        mocks.reset.mockRejectedValueOnce(new Error('Widget did not render.'));
        await submitForm();
        expectRecovery();
        await submitForm();
        expect(container.querySelector('button[type=submit]')?.hasAttribute('disabled')).toBe(true);
        mocks.register.mockReturnValue(new Promise(() => {}));
        await act(async () => {
            mocks.captcha.onVerify('verified-token');
            mocks.captcha.onVerify('verified-token');
        });
        expect(mocks.register).toHaveBeenCalledExactlyOnceWith(
            expect.objectContaining({
                username: values.username,
                'g-recaptcha-response': 'verified-token',
            }),
        );
        await act(async () => {
            mocks.captcha.onError();
            mocks.captcha.onExpire();
        });
        expect(container.querySelector('button[type=submit]')?.hasAttribute('disabled')).toBe(true);
        expect(mocks.addError).toHaveBeenCalledTimes(1);
    });

    it('recovers when the widget ref is missing', async () => {
        mocks.missingWidget = true;
        await enableCaptcha();
        await submitForm();
        expectRecovery();
    });

    it('recovers from a synchronous execution failure', async () => {
        await enableCaptcha();
        mocks.execute.mockImplementationOnce(() => {
            throw new Error('Provider unavailable.');
        });
        await submitForm();
        expectRecovery();
    });

    it.each(['onError', 'onExpire'] as const)('recovers when the provider calls %s', async (event) => {
        await enableCaptcha();
        await submitForm();
        await act(async () => mocks.captcha[event]());
        expectRecovery();
        await act(async () => mocks.captcha.onVerify('stale-token'));
        expect(mocks.register).not.toHaveBeenCalled();
    });

    it.each(['', '   ', undefined])(
        'rejects a missing verification token %j without submitting registration',
        async (token) => {
            await enableCaptcha();
            await submitForm();
            await act(async () => mocks.captcha.onVerify(token as string));
            expectRecovery();
        },
    );
});
