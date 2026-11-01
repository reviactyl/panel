import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import Title from './Title';
import ContentBox from '@/reviactyl/elements/ContentBox';

const state = vi.hoisted(() => ({ modernHeaders: false }));
vi.mock('./useModernHeaders', () => ({ default: () => state.modernHeaders }));
vi.mock('@/reviactyl/ui/Card', () => ({
    default: ({ children }: { children: ReactNode }) => <section>{children}</section>,
}));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));

describe('styled heading semantics', () => {
    let container: HTMLDivElement;
    let root: Root;
    beforeEach(() => {
        state.modernHeaders = false;
        container = document.createElement('div');
        root = createRoot(container);
    });
    afterEach(async () => {
        await act(async () => root.unmount());
    });

    it('uses an h1 without changing the classic title classes', async () => {
        await act(async () =>
            root.render(
                <Title as='h1' className='text-4xl font-bold!'>
                    Your Servers
                </Title>,
            ),
        );
        const heading = container.querySelector('h1');
        expect(heading?.textContent).toBe('Your Servers');
        expect(heading?.classList.contains('text-4xl')).toBe(true);
        expect(heading?.classList.contains('bg-clip-text')).toBe(true);
    });

    it('keeps section headings when switching from modern to classic', async () => {
        state.modernHeaders = true;
        await act(async () =>
            root.render(
                <ContentBox insetTitle title='SSH Keys'>
                    Keys
                </ContentBox>,
            ),
        );
        expect(container.querySelector('section h2')?.textContent).toBe('SSH Keys');
        state.modernHeaders = false;
        await act(async () =>
            root.render(
                <ContentBox insetTitle title='SSH Keys'>
                    Keys
                </ContentBox>,
            ),
        );
        expect(container.querySelector('h2')?.textContent).toBe('SSH Keys');
        expect(container.querySelector('section h2')).toBeNull();
        expect(container.querySelectorAll('h2')).toHaveLength(1);
    });
});
