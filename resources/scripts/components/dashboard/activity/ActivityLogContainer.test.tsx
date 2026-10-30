import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import ActivityLogContainer from './ActivityLogContainer';

const mocks = vi.hoisted(() => ({
    response: {} as { data?: { items: never[]; pagination: { total: number } }; isValidating?: boolean; error?: Error },
    flash: vi.fn(),
    pages: [] as number[],
    expiredPage: false,
}));
vi.mock('@/api/account/activity', () => ({
    useActivityLogs: ({ page }: { page: number }) => {
        mocks.pages.push(page);
        return mocks.expiredPage && page > 1
            ? { data: { items: [], pagination: { total: 10 } }, isValidating: false }
            : mocks.response;
    },
}));
vi.mock('@/plugins/useFlash', () => ({ useFlashKey: () => ({ clearAndAddHttpError: mocks.flash }) }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/dashboard/AccountContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/table/PaginationFooter', () => ({
    default: ({ onPageSelect }: { onPageSelect: (page: number) => void }) => (
        <button onClick={() => onPageSelect(2)}>Page 2</button>
    ),
}));
vi.mock('@/reviactyl/elements/activity/ActivityLogEntry', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Spinner', () => ({ default: () => <span>Loading</span> }));

describe('account activity states', () => {
    let container: HTMLDivElement;
    let root: Root;

    beforeEach(() => {
        mocks.response = { data: { items: [], pagination: { total: 0 } }, isValidating: false };
        mocks.flash.mockClear();
        mocks.pages = [];
        mocks.expiredPage = false;
        container = document.createElement('div');
        document.body.appendChild(container);
        root = createRoot(container);
    });

    afterEach(async () => {
        await act(async () => root.unmount());
        container.remove();
    });

    const render = async (hash = '') => {
        await act(async () => {
            root.render(
                <MemoryRouter initialEntries={[`/account/activity${hash}`]}>
                    <ActivityLogContainer />
                </MemoryRouter>,
            );
        });
    };

    it('shows an explicit empty state after a successful empty response', async () => {
        await render();
        expect(container.textContent).toContain('activity.no-logs');
        expect(container.textContent).not.toContain('activity.no-matches');
    });

    it.each(['#event=auth:fail', '#ip=192.0.2.1'])('clears a no-match filter from %s', async (hash) => {
        await render(hash);
        expect(container.textContent).toContain('activity.no-matches');
        await act(async () => container.querySelector('a')!.click());
        expect(container.textContent).toContain('activity.no-logs');
        expect(container.textContent).not.toContain('activity.clear-filters');
    });

    it('returns to page one when a later page becomes empty but results still exist', async () => {
        mocks.expiredPage = true;
        await render();
        await act(async () => container.querySelector('button')!.click());
        expect(mocks.pages).toContain(2);
        expect(mocks.pages[mocks.pages.length - 1]).toBe(1);
    });

    it('does not present loading or a failed request as an empty history', async () => {
        mocks.response = { isValidating: true };
        await render();
        expect(container.textContent).toContain('Loading');
        expect(container.textContent).not.toContain('activity.no-logs');
        const error = new Error('Request failed');
        mocks.response = { isValidating: false, error };
        await render();
        expect(mocks.flash).toHaveBeenCalledWith(error);
        expect(container.textContent).not.toContain('activity.no-logs');
    });
});
