import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { beforeEach, afterEach, describe, expect, it, vi } from 'vitest';
import MoveServersModal from './MoveServersModal';
import { Server } from '@/api/server/getServer';
const mocks = vi.hoisted(() => ({
    clear: vi.fn(() => ({ type: 'clear' })),
    update: vi.fn(),
    error: vi.fn(),
    saved: vi.fn(),
    dismissed: vi.fn(),
}));
vi.mock('@/api/server/updateServerCategory', () => ({ default: mocks.update }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: mocks.clear, clearAndAddHttpError: mocks.error }),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/dialog', () => ({
    Dialog: Object.assign(({ children }: { children: ReactNode }) => children, {
        Footer: ({ children }: { children: ReactNode }) => children,
    }),
}));
describe('single server folder move', () => {
    let container: HTMLDivElement;
    let root: Root;
    beforeEach(async () => {
        vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
        vi.clearAllMocks();
        mocks.update.mockReset().mockResolvedValue(undefined);
        mocks.saved.mockResolvedValue(undefined);
        container = document.createElement('div');
        document.body.appendChild(container);
        root = createRoot(container);
        await act(async () =>
            root.render(
                <MoveServersModal
                    server={{ uuid: 'server', category: { uuid: 'source' } } as Server}
                    categories={[
                        {
                            uuid: 'source',
                            name: 'Source',
                            description: null,
                            color: null,
                            createdAt: new Date(),
                            updatedAt: new Date(),
                        },
                    ]}
                    onDismissed={mocks.dismissed}
                    onSaved={mocks.saved}
                />,
            ),
        );
    });
    afterEach(async () => {
        await act(async () => root.unmount());
        container.remove();
        vi.unstubAllGlobals();
    });
    const move = async () => {
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((b) => b.textContent === 'folders.home')!
                .click(),
        );
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((b) => b.textContent === 'folders.confirm-move')!
                .click(),
        );
    };
    it('marks the current folder and moves only the hovered server', async () => {
        expect(container.textContent).toContain('folders.current');
        expect(container.querySelectorAll('input[type="checkbox"]')).toHaveLength(0);
        await move();
        expect(mocks.update).toHaveBeenCalledWith('server', null);
        expect(mocks.saved).toHaveBeenCalledOnce();
        expect(mocks.dismissed).toHaveBeenCalledOnce();
    });
    it('keeps the picker open when the assignment fails', async () => {
        mocks.update.mockRejectedValueOnce(new Error('Failed'));
        await move();
        expect(mocks.error).toHaveBeenCalledOnce();
        expect(mocks.dismissed).not.toHaveBeenCalled();
    });
    it('closes after a successful assignment even when refreshing the list fails', async () => {
        mocks.saved.mockRejectedValueOnce(new Error('Refresh failed'));
        await move();
        expect(mocks.update).toHaveBeenCalledOnce();
        expect(mocks.dismissed).toHaveBeenCalledOnce();
        expect(mocks.error).toHaveBeenCalledWith(expect.objectContaining({ key: 'dashboard' }));
    });
});
