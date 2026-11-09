import { act, type PropsWithChildren, type ReactElement } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { fireEvent, getByRole } from '@testing-library/dom';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import CopyOnClick from './CopyOnClick';
import Input from './Input';
import Blur from '@/reviactyl/ui/Blur';
import { createStore, StoreProvider } from 'easy-peasy';

const copy = vi.hoisted(() => vi.fn());
vi.mock('copy-to-clipboard', () => ({ default: copy }));
vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children }: PropsWithChildren) => children,
    motion: { div: ({ children }: PropsWithChildren) => <div>{children}</div> },
}));

let container: HTMLDivElement;
let portal: HTMLDivElement;
let root: Root;

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    container = document.createElement('div');
    portal = document.createElement('div');
    portal.id = 'modal-portal';
    document.body.append(container, portal);
    root = createRoot(container);
    copy.mockReset();
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    portal.remove();
    vi.unstubAllGlobals();
});

it.each([
    ['database name', 'database-name', 'database-name'],
    ['TOTP secret', 'JBSWY3DPEHPK3PXP', 'JBSW Y3DP EHPK 3PXP'],
])('copies a %s with Enter and Space', async (_, text, displayed) => {
    await act(async () => {
        root.render(
            <CopyOnClick text={text}>
                <p className='text-sm'>{displayed}</p>
            </CopyOnClick>,
        );
    });

    const action = getByRole(container, 'button', { name: displayed });
    expect(action.tabIndex).toBe(0);
    action.focus();
    expect(document.activeElement).toBe(action);
    expect(action.classList.contains('text-sm')).toBe(true);

    for (const key of ['Enter', ' ']) {
        const event = new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true });
        await act(async () => action.dispatchEvent(event));
        expect(event.defaultPrevented).toBe(true);
    }
    expect(copy).toHaveBeenNthCalledWith(1, text);
    expect(copy).toHaveBeenNthCalledWith(2, text);
    expect(portal.textContent).toContain(`Copied "${text}" to clipboard.`);
});

it.each(['Enter', ' '])('preserves text handlers and honors prevented %s events', async (key) => {
    const onClick = vi.fn();
    const onKeyDown = vi.fn((event: React.KeyboardEvent) => event.preventDefault());
    const render = (prevented: boolean) => (
        <CopyOnClick text='database-name'>
            <p onClick={onClick} onKeyDown={prevented ? onKeyDown : undefined}>
                Database name
            </p>
        </CopyOnClick>
    );
    await act(async () => root.render(render(true)));
    const action = container.querySelector('p')!;
    await act(async () => fireEvent.keyDown(action, { key }));
    expect(onKeyDown).toHaveBeenCalledOnce();
    expect(copy).not.toHaveBeenCalled();

    await act(async () => root.render(render(false)));
    await act(async () => fireEvent.keyDown(action, { key, repeat: true }));
    await act(async () => fireEvent.keyDown(action, { key: 'ArrowDown' }));
    expect(copy).not.toHaveBeenCalled();
    await act(async () => fireEvent.keyDown(action, { key }));
    expect(copy).toHaveBeenCalledOnce();
    expect(onClick).toHaveBeenCalledOnce();
});

it.each(['input', 'button', 'textarea', 'select', 'custom input'])('preserves native %s behavior', async (kind) => {
    const onClick = vi.fn();
    const onKeyDown = vi.fn();
    const props = { 'aria-label': 'Native control', onClick, onKeyDown };
    const children: Record<string, ReactElement> = {
        input: <input {...props} defaultValue='editable' />,
        button: <button {...props}>Native control</button>,
        textarea: <textarea {...props} defaultValue='editable' />,
        select: (
            <select {...props}>
                <option>Option</option>
            </select>
        ),
        'custom input': <Input {...props} defaultValue='editable' />,
    };
    await act(async () => root.render(<CopyOnClick text='value'>{children[kind]}</CopyOnClick>));
    const control = container.firstElementChild as HTMLElement;
    expect(control.getAttribute('role')).toBeNull();
    expect(control.getAttribute('tabindex')).toBeNull();
    control.focus();
    expect(document.activeElement).toBe(control);
    const event = new KeyboardEvent('keydown', { key: ' ', bubbles: true, cancelable: true });
    await act(async () => control.dispatchEvent(event));
    expect(event.defaultPrevented).toBe(false);
    expect(onKeyDown).toHaveBeenCalledOnce();
    expect(copy).not.toHaveBeenCalled();
    await act(async () => fireEvent.click(control));
    expect(onClick).toHaveBeenCalledOnce();
    expect(copy).toHaveBeenCalledWith('value');
});

it('keeps contenteditable text editable and ignores keys from nested controls', async () => {
    await act(async () => {
        root.render(
            <>
                <CopyOnClick text='editable'>
                    <div contentEditable suppressContentEditableWarning>
                        Editable
                    </div>
                </CopyOnClick>
                <CopyOnClick text='outer'>
                    <div>
                        <input aria-label='Nested input' />
                    </div>
                </CopyOnClick>
            </>,
        );
    });
    expect(container.firstElementChild!.getAttribute('role')).toBeNull();
    const input = getByRole(container, 'textbox', { name: 'Nested input' });
    await act(async () => fireEvent.keyDown(input, { key: ' ' }));
    expect(copy).not.toHaveBeenCalled();
});

it('preserves mouse copying and masks secret notifications', async () => {
    await act(async () => {
        root.render(
            <CopyOnClick text='secret-value' showInNotification={false}>
                <p>Hidden secret</p>
            </CopyOnClick>,
        );
    });
    await act(async () => fireEvent.click(container.querySelector('p')!));
    expect(copy).toHaveBeenCalledWith('secret-value');
    expect(portal.textContent).toContain('Copied text to clipboard.');
    expect(portal.textContent).not.toContain('secret-value');
});

it('does not add copy behavior when text is absent', async () => {
    await act(async () =>
        root.render(
            <CopyOnClick text={undefined}>
                <p>Loading</p>
            </CopyOnClick>,
        ),
    );
    expect(container.querySelector('p')!.tabIndex).toBe(-1);
    await act(async () => fireEvent.click(container.querySelector('p')!));
    expect(copy).not.toHaveBeenCalled();
});

it('copies the server address rendered through Blur with Enter and Space', async () => {
    const store = createStore({ designify: { data: { allocationBlur: true } } });
    await act(async () =>
        root.render(
            <StoreProvider store={store}>
                <CopyOnClick text='example.test:25565'>
                    <Blur>example.test:25565</Blur>
                </CopyOnClick>
            </StoreProvider>,
        ),
    );
    const action = getByRole(container, 'button', { name: 'example.test:25565' });
    expect(action.tabIndex).toBe(0);
    for (const key of ['Enter', ' ']) {
        await act(async () => fireEvent.keyDown(action, { key }));
    }
    expect(copy).toHaveBeenCalledTimes(2);
    expect(copy).toHaveBeenLastCalledWith('example.test:25565');
});
