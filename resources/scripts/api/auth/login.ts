import http from '@/api/http';

const getXsrfToken = (): string | undefined => {
    const tokens = document.cookie
        .split(';')
        .map((cookie) => cookie.trim())
        .filter((cookie) => cookie.startsWith('XSRF-TOKEN='))
        .map((cookie) => cookie.slice('XSRF-TOKEN='.length));

    if (tokens.length === 0) return undefined;

    const token = tokens[tokens.length - 1];

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
        throw new Error('Unable to locate an XSRF token for login.');
    }

    return refreshedToken;
};

export interface LoginResponse {
    complete: boolean;
    intended?: string;
    confirmationToken?: string;
}

export interface LoginData {
    username: string;
    password: string;
    captchaToken?: string | null;
    captchaProvider?: string;
}

export default ({ username, password, captchaToken, captchaProvider }: LoginData): Promise<LoginResponse> => {
    return new Promise((resolve, reject) => {
        // Build captcha response field based on provider
        const captchaField =
            captchaProvider === 'turnstile'
                ? { 'cf-turnstile-response': captchaToken }
                : { 'g-recaptcha-response': captchaToken };

        resolveXsrfToken()
            .then((xsrfToken) =>
                http.post(
                    '/auth/login',
                    {
                        user: username,
                        password,
                        ...captchaField,
                    },
                    {
                        headers: {
                            'X-XSRF-TOKEN': xsrfToken,
                        },
                        // Avoid ambiguous X-XSRF-TOKEN when duplicate cookies exist in tunneled environments.
                        xsrfCookieName: '__reviactyl_ignore_xsrf_cookie__',
                    }
                )
            )
            .then((response) => {
                if (!(response.data instanceof Object)) {
                    return reject(new Error('An error occurred while processing the login request.'));
                }

                return resolve({
                    complete: response.data.data.complete,
                    intended: response.data.data.intended || undefined,
                    confirmationToken: response.data.data.confirmation_token || undefined,
                });
            })
            .catch(reject);
    });
};
