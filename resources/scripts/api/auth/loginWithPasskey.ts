import http from '@/api/http';
import type { LoginResponse } from '@/api/auth/login';
import { createPasskeyAssertion, isPasskeySupported } from '@/lib/webauthn';

const getXsrfToken = (): string | undefined => {
    const tokens = document.cookie
        .split(';')
        .map((cookie) => cookie.trim())
        .filter((cookie) => cookie.startsWith('XSRF-TOKEN='))
        .map((cookie) => cookie.slice('XSRF-TOKEN='.length));

    const token = tokens[tokens.length - 1];

    if (!token) return undefined;

    try {
        return decodeURIComponent(token);
    } catch {
        return token;
    }
};

const resolveXsrfToken = async (): Promise<string> => {
    const existingToken = getXsrfToken();
    if (existingToken) {
        return existingToken;
    }

    await http.get('/sanctum/csrf-cookie');

    const refreshedToken = getXsrfToken();
    if (!refreshedToken) {
        throw new Error('Unable to locate an XSRF token for passkey login.');
    }

    return refreshedToken;
};

const mapPasskeyAssertionError = (error: unknown): Error => {
    const errorName =
        typeof error === 'object' && error !== null && 'name' in error ? String((error as { name: unknown }).name) : '';
    const errorMessage =
        typeof error === 'object' && error !== null && 'message' in error
            ? String((error as { message: unknown }).message)
            : '';

    if (errorName === 'NotAllowedError' || /timed out|not allowed/i.test(errorMessage)) {
        return new Error('PASSKEY_NO_CREDENTIAL');
    }

    if (errorName === 'SecurityError' || errorName === 'InvalidStateError') {
        return new Error('PASSKEY_SECURITY_ERROR');
    }

    return error instanceof Error ? error : new Error('PASSKEY_LOGIN_FAILED');
};

export default async (username?: string): Promise<LoginResponse> => {
    if (!isPasskeySupported()) {
        throw new Error('Passkeys are not supported by this browser.');
    }

    const xsrfToken = await resolveXsrfToken();

    const optionsResponse = await http.post(
        '/auth/login/passkey/options',
        {
            user: username && username.length > 0 ? username : undefined,
        },
        {
            headers: {
                'X-XSRF-TOKEN': xsrfToken,
            },
            // Avoid ambiguous X-XSRF-TOKEN when duplicate cookies exist in tunneled environments.
            xsrfCookieName: '__reviactyl_ignore_xsrf_cookie__',
        }
    );

    let assertion;

    try {
        assertion = await createPasskeyAssertion(optionsResponse.data);
    } catch (error) {
        throw mapPasskeyAssertionError(error);
    }

    const loginResponse = await http.post('/auth/login/passkey', assertion, {
        headers: {
            'X-XSRF-TOKEN': xsrfToken,
        },
        xsrfCookieName: '__reviactyl_ignore_xsrf_cookie__',
    });

    return {
        complete: loginResponse.data.data.complete,
        intended: loginResponse.data.data.intended || undefined,
        confirmationToken: loginResponse.data.data.confirmation_token || undefined,
    };
};
