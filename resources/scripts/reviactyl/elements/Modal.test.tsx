import { act, useState, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import Modal from './Modal';

vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
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

let root: Root;
let page: HTMLDivElement;
let portal: HTMLDivElement;
const dismissed = vi.fn();
function Harness({ locked = false, customFocus = false }: { locked?: boolean; customFocus?: boolean }) {
    const [open, setOpen] = useState(false);
    const [nested, setNested] = useState(false);
    return (
        <>
            <button onClick={() => setOpen(true)}>Open</button>
            <button>Background action</button>
            <Modal
                visible={open}
                dismissable={!locked}
                onDismissed={() => {
                    dismissed();
                    setOpen(false);
                }}
            >
                <h2
                    tabIndex={-1}
                    ref={(node) => {
                        if (customFocus) node?.focus();
                    }}
                >
                    Parent dialog
                </h2>
                <input aria-label='Name' />
                <button onClick={() => setNested(true)}>Open nested</button>
                <Modal visible={nested} onDismissed={() => setNested(false)}>
                    <h2>Nested dialog</h2>
                    <input aria-label='Nested name' />
                </Modal>
            </Modal>
        </>
    );
}
beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.useFakeTimers();
    dismissed.mockClear();
    page = document.createElement('div');
    portal = document.createElement('div');
    portal.id = 'modal-portal';
    document.body.append(page, portal);
    root = createRoot(page);
});
afterEach(async () => {
    await act(async () => root.unmount());
    page.remove();
    portal.remove();
    vi.useRealTimers();
    vi.unstubAllGlobals();
});
async function click(button: HTMLButtonElement) {
    button.focus();
    await act(async () => button.click());
}
async function escape() {
    await act(async () =>
        document.activeElement!.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true })),
    );
}
async function finishFade() {
    await act(async () => vi.advanceTimersByTimeAsync(150));
}
it('places focus inside a named modal, makes the page inert, and restores focus after the fade', async () => {
    await act(async () => root.render(<Harness />));
    const trigger = page.querySelector('button')!;
    await click(trigger);
    expect(page.contains(document.activeElement)).toBe(false);
    const dialog = document.querySelector('[role=dialog]')!;
    expect(dialog.getAttribute('aria-modal')).toBe('true');
    expect(document.getElementById(dialog.getAttribute('aria-labelledby')!)?.textContent).toBe('Parent dialog');
    expect(document.activeElement).toBe(dialog.querySelector('input'));
    expect(page.inert).toBe(true);
    expect(page.getAttribute('aria-hidden')).toBe('true');
    await escape();
    expect(dismissed).not.toHaveBeenCalled();
    expect(page.inert).toBe(true);
    await finishFade();
    expect(dismissed).toHaveBeenCalledOnce();
    expect(document.querySelector('[role=dialog]')).toBeNull();
    expect(page.inert).toBe(false);
    expect(page.hasAttribute('aria-hidden')).toBe(false);
    expect(document.activeElement).toBe(trigger);
});
it('preserves a caller-selected initial focus target', async () => {
    await act(async () => root.render(<Harness customFocus />));
    await click(page.querySelector('button')!);
    expect(document.activeElement).toBe(document.querySelector('[role=dialog] h2'));
});
it('dismisses only the nested dialog and restores its opener before restoring the page', async () => {
    await act(async () => root.render(<Harness />));
    const trigger = page.querySelector('button')!;
    await click(trigger);
    const nestedTrigger = Array.from(document.querySelectorAll('button')).find(
        (button) => button.textContent === 'Open nested',
    )!;
    await click(nestedTrigger);
    expect(document.querySelectorAll('[role=dialog]')).toHaveLength(2);
    expect(document.activeElement?.getAttribute('aria-label')).toBe('Nested name');
    await escape();
    await finishFade();
    expect(document.querySelectorAll('[role=dialog]')).toHaveLength(1);
    expect(dismissed).not.toHaveBeenCalled();
    expect(document.activeElement).toBe(nestedTrigger);
    expect(page.inert).toBe(true);
    await escape();
    await finishFade();
    expect(document.activeElement).toBe(trigger);
});
it('keeps locked dialogs open on Escape and releases the page on unmount', async () => {
    await act(async () => root.render(<Harness locked />));
    await click(page.querySelector('button')!);
    await escape();
    await finishFade();
    expect(dismissed).not.toHaveBeenCalled();
    expect(document.querySelector('[role=dialog]')).not.toBeNull();
    await act(async () => root.render(null));
    expect(page.inert).toBe(false);
});
