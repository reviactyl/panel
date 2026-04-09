import http from '@/api/http';
import { createPasskeyCredential, isPasskeySupported } from '@/lib/webauthn';

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

    const optionsResponse = await http.post('/api/client/account/passkeys/register/options', {
        password,
    });

    const attestation = await createPasskeyCredential(optionsResponse.data);

    await http.post('/api/client/account/passkeys/register', {
        ...attestation,
        name: name && name.length > 0 ? name : undefined,
    });
};

export const deleteAccountPasskey = async (id: string, password: string): Promise<void> => {
    await http.delete(`/api/client/account/passkeys/${id}`, {
        data: {
            password,
        },
    });
};
