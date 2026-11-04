import { act, type ReactNode } from 'react';
import { createRoot } from 'react-dom/client';
import { SWRConfig } from 'swr';
import { expect, it, vi } from 'vitest';
import NotificationCenter from './NotificationCenter';

const get = vi.hoisted(() => vi.fn());
vi.mock('@/api/http', () => ({ default: { get } }));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key, i18n: { language: 'en' } }) }));
vi.mock('react-router-dom', () => ({
    Link: ({ children, to }: { children: ReactNode; to: string }) => <a href={to}>{children}</a>,
}));
vi.mock('@headlessui/react', () => ({
    Dialog: ({ children, open }: { children: ReactNode; open: boolean }) => (open ? <div>{children}</div> : null),
    DialogBackdrop: () => null,
    DialogPanel: ({ children }: { children: ReactNode }) => <div>{children}</div>,
    DialogTitle: ({ children }: { children: ReactNode }) => <h2>{children}</h2>,
}));

it('keeps the list visible while a different filter loads, then shows the new result', async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    let finish: (value: unknown) => void = () => {};
    const pending = new Promise((resolve) => {
        finish = resolve;
    });
    get.mockResolvedValueOnce({
        data: {
            data: [
                {
                    id: 'one',
                    data: { event: 'installed', server: 'Example server', url: '/' },
                    read_at: null,
                    created_at: '2026-11-04T00:00:00Z',
                },
            ],
            unread: 1,
            last_page: 1,
        },
    }).mockReturnValueOnce(pending);
    const container = document.createElement('div');
    const root = createRoot(container);
    const click = async (label: string) => {
        const button = Array.from(container.querySelectorAll('button')).find((node) => node.textContent === label);
        expect(button).toBeDefined();
        await act(async () => button!.click());
    };
    try {
        await act(async () =>
            root.render(
                <SWRConfig value={{ provider: () => new Map(), dedupingInterval: 0 }}>
                    <NotificationCenter />
                </SWRConfig>,
            ),
        );
        await click('title1');
        expect(container.textContent).toContain('Example server');
        await click('unread');
        expect(container.textContent).toContain('Example server');
        expect(container.textContent).not.toContain('loading');
        await act(async () => finish({ data: { data: [], unread: 0, last_page: 1 } }));
        expect(container.textContent).not.toContain('Example server');
        expect(container.textContent).toContain('caught-up');
    } finally {
        await act(async () => root.unmount());
    }
});
