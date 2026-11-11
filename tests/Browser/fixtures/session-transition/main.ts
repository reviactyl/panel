import http, { resetHttpSession, withHttpSessionTransition } from '@/api/http';

export let workStatus = 'idle';

export const startWork = () => {
    void http.post('/api/client/work', {}, { timeout: 0, signal: new AbortController().signal }).then(
        () => (workStatus = 'complete'),
        (error) => (workStatus = error.code),
    );
};

export const logout = () =>
    withHttpSessionTransition(async () => {
        await http.post('/auth/logout');
        resetHttpSession();
    });
