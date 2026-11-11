import http from '@/api/http';
import { LoginResponse } from '@/api/auth/login';

export default (token: string, code: string, recoveryToken?: string): Promise<LoginResponse> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/login/checkpoint', {
            confirmation_token: token,
            authentication_code: code,
            recovery_token: recoveryToken && recoveryToken.length > 0 ? recoveryToken : undefined,
        })
            .then((response) =>
                resolve({
                    complete: response.data.data.complete,
                    user: response.data.data.user,
                    csrfToken: response.data.data.csrf_token,
                    twoFactorRequired: response.data.data.two_factor_required,
                    intended: response.data.data.intended || undefined,
                }),
            )
            .catch(reject);
    });
};
