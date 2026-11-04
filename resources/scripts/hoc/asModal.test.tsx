import { act, useContext, useState, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import ModalContext from '@/context/ModalContext';
import asModal from './asModal';

vi.mock('@/reviactyl/elements/transitions/FadeTransition', async () => {
    const { useEffect, useState } = await import('react');

    return {
        default: ({ show, children, afterLeave }: { show: boolean; children: ReactNode; afterLeave?: () => void }) => {
            const [rendered, setRendered] = useState(show);

            useEffect(() => {
                if (show) {
                    setRendered(true);
                    return;
                }
                if (!rendered) return;
                const timer = setTimeout(() => {
                    setRendered(false);
                    afterLeave?.();
                }, 150);
                return () => clearTimeout(timer);
            }, [show, rendered, afterLeave]);

            return show || rendered ? children : null;
        },
    };
});

const dismissed = vi.fn();
const Content = () => {
    const { dismiss, setPropOverrides } = useContext(ModalContext);

    return (
        <>
            <p>Modal content</p>
            <button onClick={dismiss}>Save</button>
            <button
                onClick={() => {
                    dismiss();
                    dismiss();
                }}
            >
                Save twice
            </button>
            <button onClick={() => setPropOverrides({ dismissable: false })}>Lock</button>
        </>
    );
};
const WrappedModal = asModal()(Content);

function Harness() {
    const [visible, setVisible] = useState(false);

    return (
        <>
            <button onClick={() => setVisible(true)}>Open</button>
            <output>{String(visible)}</output>
            <WrappedModal
                visible={visible}
                onModalDismissed={() => {
                    dismissed();
                    setVisible(false);
                }}
            />
        </>
    );
}

let root: Root;
let container: HTMLDivElement;
let portal: HTMLDivElement;

beforeEach(async () => {
    vi.useFakeTimers();
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    dismissed.mockClear();
    container = document.createElement('div');
    portal = document.createElement('div');
    portal.id = 'modal-portal';
    document.body.append(container, portal);
    root = createRoot(container);
    await act(async () => root.render(<Harness />));
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    portal.remove();
    vi.unstubAllGlobals();
    vi.useRealTimers();
});

async function clickButton(label: string) {
    const button = Array.from(document.querySelectorAll('button')).find((element) => element.textContent === label);
    expect(button).toBeTruthy();
    await act(async () => button!.click());
}

async function finishClose(count = 1) {
    expect(portal.textContent).toContain('Modal content');
    expect(dismissed).toHaveBeenCalledTimes(count - 1);
    await act(async () => vi.advanceTimersByTimeAsync(149));
    expect(portal.textContent).toContain('Modal content');
    expect(dismissed).toHaveBeenCalledTimes(count - 1);
    await act(async () => vi.advanceTimersByTimeAsync(1));
    expect(portal.textContent).not.toContain('Modal content');
    expect(dismissed).toHaveBeenCalledTimes(count);
}

it('synchronizes a successful programmatic close and allows repeated reopening', async () => {
    for (let count = 1; count <= 2; count++) {
        await clickButton('Open');
        expect(portal.textContent).toContain('Modal content');
        await clickButton('Save');
        await finishClose(count);
        expect(portal.textContent).not.toContain('Modal content');
        expect(container.querySelector('output')!.textContent).toBe('false');
        expect(dismissed).toHaveBeenCalledTimes(count);
    }
    await clickButton('Open');
    expect(portal.textContent).toContain('Modal content');
});

it('notifies once when programmatic dismissal is requested twice', async () => {
    await clickButton('Open');
    await clickButton('Save twice');
    await finishClose();
    expect(dismissed).toHaveBeenCalledOnce();
});

it.each(['escape', 'background', 'close'])('notifies once after manual %s dismissal and reopens', async (method) => {
    await clickButton('Open');
    await act(async () => {
        if (method === 'escape') {
            window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
        } else if (method === 'background') {
            portal.firstElementChild!.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
        } else {
            portal.querySelector<SVGElement>('svg')!.dispatchEvent(new MouseEvent('click', { bubbles: true }));
        }
    });
    await finishClose();
    expect(dismissed).toHaveBeenCalledOnce();
    expect(container.querySelector('output')!.textContent).toBe('false');
    await clickButton('Open');
    expect(portal.textContent).toContain('Modal content');
});

it('clears overrides when a locked modal is dismissed programmatically', async () => {
    await clickButton('Open');
    await clickButton('Lock');
    expect(portal.querySelector('svg')).toBeNull();
    await clickButton('Save');
    await finishClose();
    await clickButton('Open');
    expect(portal.querySelector('svg')).not.toBeNull();
});
