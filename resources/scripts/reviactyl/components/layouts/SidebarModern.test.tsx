import React, { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { getByRole, queryByRole } from '@testing-library/dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { SidebarModern } from './SidebarModern';
import http from '@/api/http';

const state = vi.hoisted(() => ({
    user: { data: { name_first: 'Demo', name_last: 'User', rootAdmin: false, adminAccess: false } },
    settings: { data: { name: 'Panel' } },
    designify: { data: { sidebarLogout: true } },
}));
vi.mock('easy-peasy', () => ({ useStoreState: (selector: (value: typeof state) => unknown) => selector(state) }));
vi.mock('react-router-dom', () => ({
    Link: ({ children, to }: React.PropsWithChildren<{ to: string }>) => <a href={to}>{children}</a>,
}));
vi.mock('react-i18next', () => ({
    useTranslation: () => ({ t: (key: string) => (key === 'overview.logout' ? 'Abmelden' : key) }),
}));
vi.mock('@/api/http', () => ({ default: { post: vi.fn() } }));
vi.mock('@/reviactyl/ui/Avatar', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/Logo', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: React.PropsWithChildren) => children,
}));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({
    default: ({ visible }: { visible: boolean }) => (visible ? <div role='status'>Loading</div> : null),
}));

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    state.designify.data.sidebarLogout = true;
    vi.mocked(http.post).mockReset();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

describe('modern sidebar logout', () => {
    it('provides a focusable native button named with the logout translation', async () => {
        await act(async () => root.render(<SidebarModern isOpen />));
        const button = getByRole(container, 'button', { name: 'Abmelden' }) as HTMLButtonElement;

        expect(button.tagName).toBe('BUTTON');
        expect(button.type).toBe('button');
        expect(button.tabIndex).toBe(0);
        await act(async () => button.focus());
        expect(document.activeElement).toBe(button);
    });

    it('keeps the logout POST and loading overlay on activation', async () => {
        vi.mocked(http.post).mockReturnValue(new Promise(() => {}));
        await act(async () => root.render(<SidebarModern isOpen />));
        expect(queryByRole(container, 'status')).toBeNull();

        await act(async () => getByRole(container, 'button', { name: 'Abmelden' }).click());

        expect(http.post).toHaveBeenCalledExactlyOnceWith('/auth/logout');
        expect(getByRole(container, 'status').textContent).toBe('Loading');
    });

    it('does not expose logout when the sidebar option is disabled', async () => {
        state.designify.data.sidebarLogout = false;
        await act(async () => root.render(<SidebarModern isOpen />));

        expect(queryByRole(container, 'button', { name: 'Abmelden' })).toBeNull();
    });
});
