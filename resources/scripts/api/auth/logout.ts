import http from '@/api/http';

const getCsrfToken = (): string | undefined => {
    const fromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')?.trim();

    if (fromMeta) {
        return fromMeta;
    }

    const fromLegacyMeta = document.querySelector('meta[name="_token"]')?.getAttribute('content')?.trim();

    return fromLegacyMeta || undefined;
};

export default async (): Promise<void> => {
    const token = getCsrfToken();

    if (!token) {
        throw new Error('Unable to locate a CSRF token for logout.');
    }

    await http.post(
        '/auth/logout',
        {
            _token: token,
        },
        {
            headers: {
                'X-CSRF-TOKEN': token,
            },
            // Prevent axios from injecting X-XSRF-TOKEN from duplicate cookies.
            xsrfCookieName: '__reviactyl_ignore_xsrf_cookie__',
        }
    );
};
