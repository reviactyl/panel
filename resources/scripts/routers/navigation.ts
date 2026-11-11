import type { createBrowserRouter } from 'react-router-dom';

let router: ReturnType<typeof createBrowserRouter>;

export const registerRouter = (value: typeof router) => {
    router = value;
};

export const navigateToLogin = () => router.navigate('/auth/login', { replace: true, flushSync: true });
