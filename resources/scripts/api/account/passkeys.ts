import http from '@/api/http';
import { createPasskeyCredential, isPasskeySupported } from '@/lib/webauthn';

const getCsrfToken = (): string | undefined => {
    const fromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')?.trim();

    if (fromMeta) {
        return fromMeta;
    }

    const fromLegacyMeta = document.querySelector('meta[name="_token"]')?.getAttribute('content')?.trim();

    if (fromLegacyMeta) {
        return fromLegacyMeta;
    }

    const token = document.cookie
        .split(';')
        .map((cookie) => cookie.trim())
        .filter((cookie) => cookie.startsWith('XSRF-TOKEN='))
        .map((cookie) => cookie.slice('XSRF-TOKEN='.length))
        .pop();

    if (!token) {
        return undefined;
    }

    try {
        return decodeURIComponent(token);
    } catch {
        return token;
    }
};

const csrfRequestConfig = async () => {
    const csrfToken = getCsrfToken();

    if (!csrfToken) {
        await http.get('/sanctum/csrf-cookie');
    }

    const resolvedToken = csrfToken ?? getCsrfToken();

    if (!resolvedToken) {
        throw new Error('Unable to locate a CSRF token for passkey operations.');
    }

    return {
        token: resolvedToken,
        config: {
            headers: {
                'X-CSRF-TOKEN': resolvedToken,
            },
            // Keep axios from adding X-XSRF-TOKEN from possibly duplicated cookies.
            xsrfCookieName: '__reviactyl_ignore_xsrf_cookie__',
        },
    };
};

export interface AccountPasskey {
    id: string;
    name: string | null;
    origin: string;
    createdAt: Date;
    updatedAt: Date;
    disabledAt: Date | null;
}

export const getAccountPasskeys = async (): Promise<AccountPasskey[]> => {
    const { data } = await http.get('/api/client/account/passkeys');

    return (data.data || []).map((item: any) => ({
        id: item.id,
        name: item.name,
        origin: item.origin,
        createdAt: new Date(item.created_at),
        updatedAt: new Date(item.updated_at),
        disabledAt: item.disabled_at ? new Date(item.disabled_at) : null,
    }));
};

export const registerAccountPasskey = async (password: string, name?: string): Promise<void> => {
    if (!isPasskeySupported()) {
        throw new Error('Passkeys are not supported by this browser.');
    }

    const { token, config } = await csrfRequestConfig();

    const optionsResponse = await http.post(
        '/api/client/account/passkeys/register/options',
        {
            password,
            _token: token,
        },
        config
    );

    const attestation = await createPasskeyCredential(optionsResponse.data);

    await http.post(
        '/api/client/account/passkeys/register',
        {
            ...attestation,
            name: name && name.length > 0 ? name : undefined,
            _token: token,
        },
        config
    );
};

export const deleteAccountPasskey = async (id: string): Promise<void> => {
    const { token, config } = await csrfRequestConfig();

    await http.post(
        '/api/client/account/passkeys/remove',
        {
            id,
            _token: token,
        },
        config
    );
};
