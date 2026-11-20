import { act, type ButtonHTMLAttributes } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { SubuserPreviewSession } from '@/api/subuserPreview';
import { SubuserPreviewFrame } from './SubuserPreviewFrame';

const mocks = vi.hoisted(() => ({ exit: vi.fn() }));
vi.mock('@/context/SubuserPreviewContext', () => ({
    useSubuserPreview: () => ({
        loading: false,
        session: {
            uuid: 'session-uuid',
            serverUuid: 'server-uuid',
            serverIdentifier: 'server',
            serverName: 'Server',
            subuserEmail: 'subuser@example.com',
            subuserUuid: 'subuser-uuid',
            permissionCount: 1,
            maxFileSize: 1024,
            powerStatus: 'offline',
            expiresAt: new Date(),
        } satisfies SubuserPreviewSession,
        exit: mocks.exit,
    }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/components/button', () => {
    const Button = (props: ButtonHTMLAttributes<HTMLButtonElement>) => <button {...props} />;
    return { Button: Object.assign(Button, { Text: Button, Sizes: { Small: 'small' } }) };
});

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

const render = async () => {
    await act(async () => root.render(<SubuserPreviewFrame>Content</SubuserPreviewFrame>));
};

const exitButton = () => container.querySelector<HTMLButtonElement>('button')!;

describe('subuser preview exit', () => {
    it('re-enables Exit Preview after a failed request and allows a successful retry', async () => {
        mocks.exit.mockRejectedValueOnce(new Error('Temporary failure')).mockResolvedValueOnce(undefined);
        await render();

        await act(async () => exitButton().click());
        expect(mocks.exit).toHaveBeenCalledOnce();
        expect(exitButton().disabled).toBe(false);
        expect(exitButton().textContent).toBe('preview.exit');

        await act(async () => exitButton().click());
        expect(mocks.exit).toHaveBeenCalledTimes(2);
    });
});
