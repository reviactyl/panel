const base64UrlToUint8Array = (value: string): Uint8Array => {
    const base64 = value.replace(/-/g, '+').replace(/_/g, '/');
    const padded = base64 + '='.repeat((4 - (base64.length % 4)) % 4);
    const binary = atob(padded);
    const bytes = new Uint8Array(binary.length);

    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }

    return bytes;
};

const arrayBufferToBase64Url = (buffer: ArrayBuffer): string => {
    const bytes = new Uint8Array(buffer);
    let binary = '';

    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i] ?? 0);
    }

    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
};

const normalizeCreationOptions = (options: any): PublicKeyCredentialCreationOptions => {
    return {
        ...options,
        challenge: base64UrlToUint8Array(options.challenge),
        user: {
            ...options.user,
            id: base64UrlToUint8Array(options.user.id),
        },
        excludeCredentials: (options.excludeCredentials || []).map((credential: any) => ({
            ...credential,
            id: base64UrlToUint8Array(credential.id),
        })),
    };
};

const normalizeRequestOptions = (options: any): PublicKeyCredentialRequestOptions => {
    return {
        ...options,
        challenge: base64UrlToUint8Array(options.challenge),
        allowCredentials: (options.allowCredentials || []).map((credential: any) => ({
            ...credential,
            id: base64UrlToUint8Array(credential.id),
        })),
    };
};

export const isPasskeySupported = (): boolean => {
    return typeof window !== 'undefined' && !!window.PublicKeyCredential && !!navigator.credentials;
};

export const createPasskeyCredential = async (options: any): Promise<Record<string, unknown>> => {
    const credential = await navigator.credentials.create({
        publicKey: normalizeCreationOptions(options),
    });

    if (!(credential instanceof PublicKeyCredential)) {
        throw new Error('Passkey registration did not return a valid credential.');
    }

    const response = credential.response as AuthenticatorAttestationResponse;

    return {
        id: credential.id,
        rawId: arrayBufferToBase64Url(credential.rawId),
        type: credential.type,
        response: {
            clientDataJSON: arrayBufferToBase64Url(response.clientDataJSON),
            attestationObject: arrayBufferToBase64Url(response.attestationObject),
        },
    };
};

export const createPasskeyAssertion = async (options: any): Promise<Record<string, unknown>> => {
    const credential = await navigator.credentials.get({
        publicKey: normalizeRequestOptions(options),
    });

    if (!(credential instanceof PublicKeyCredential)) {
        throw new Error('Passkey login did not return a valid credential.');
    }

    const response = credential.response as AuthenticatorAssertionResponse;

    return {
        id: credential.id,
        rawId: arrayBufferToBase64Url(credential.rawId),
        type: credential.type,
        response: {
            authenticatorData: arrayBufferToBase64Url(response.authenticatorData),
            clientDataJSON: arrayBufferToBase64Url(response.clientDataJSON),
            signature: arrayBufferToBase64Url(response.signature),
            userHandle: response.userHandle ? arrayBufferToBase64Url(response.userHandle) : null,
        },
    };
};
