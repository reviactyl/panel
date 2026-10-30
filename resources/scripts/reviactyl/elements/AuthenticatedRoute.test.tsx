import { act } from 'react';
import { createRoot } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, expect, it, vi } from 'vitest';
import AuthenticatedRoute from './AuthenticatedRoute';

const state = vi.hoisted(() => ({ authenticated: false }));
vi.mock('@/state/hooks', () => ({ useStoreState: () => state.authenticated }));
afterEach(() => vi.restoreAllMocks());

it.each([false, true])('handles protected SPA navigation with authenticated=%s', async (authenticated) => {
    state.authenticated = authenticated;
    const replace = vi.spyOn(window.location, 'replace').mockImplementation(() => {});
    const container = document.createElement('div');
    const root = createRoot(container);
    await act(async () => {
        root.render(
            <MemoryRouter initialEntries={['/account?tab=security#keys']}>
                <AuthenticatedRoute>Protected content</AuthenticatedRoute>
            </MemoryRouter>,
        );
    });
    if (authenticated) {
        expect(container.textContent).toBe('Protected content');
        expect(replace).not.toHaveBeenCalled();
    } else {
        expect(container.textContent).toBe('');
        expect(replace).toHaveBeenCalledWith('/account?tab=security#keys');
    }
    await act(async () => root.unmount());
});
