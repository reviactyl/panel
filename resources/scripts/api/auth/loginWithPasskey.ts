import http from '@/api/http';
import type { LoginResponse } from '@/api/auth/login';
import { createPasskeyAssertion, isPasskeySupported } from '@/lib/webauthn';

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

    await http.get('/sanctum/csrf-cookie');

    const optionsResponse = await http.post('/auth/login/passkey/options', {
        user: username && username.length > 0 ? username : undefined,
    });

    let assertion;

    try {
        assertion = await createPasskeyAssertion(optionsResponse.data);
    } catch (error) {
        throw mapPasskeyAssertionError(error);
    }

    const loginResponse = await http.post('/auth/login/passkey', assertion);

    return {
        complete: loginResponse.data.data.complete,
        intended: loginResponse.data.data.intended || undefined,
        confirmationToken: loginResponse.data.data.confirmation_token || undefined,
    };
};
