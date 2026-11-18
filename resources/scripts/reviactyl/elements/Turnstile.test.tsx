import { act, createRef, StrictMode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import Turnstile, { type TurnstileHandle } from './Turnstile';

let root: Root;
let container: HTMLDivElement;
const verify = vi.fn();
const expire = vi.fn();
const error = vi.fn();
const api = () => ({
    render: vi.fn((_container: HTMLElement, _options: unknown) => 'widget-0'),
    reset: vi.fn(),
    remove: vi.fn(),
});
const mount = async (siteKey = 'site-key') => {
    await act(async () =>
        root.render(<Turnstile siteKey={siteKey} onVerify={verify} onExpire={expire} onError={error} />),
    );
};
const pendingScript = () => {
    const script = document.createElement('script');
    script.type = 'application/json';
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
    document.head.appendChild(script);
    return script;
};

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    vi.clearAllMocks();
    const createElement = document.createElement.bind(document);
    vi.spyOn(document, 'createElement').mockImplementation((tag, options) => {
        const element = createElement(tag, options);
        if (element instanceof HTMLScriptElement) element.type = 'application/json';
        return element;
    });
    document.documentElement.className = 'light';
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    document
        .querySelectorAll('script[src*="challenges.cloudflare.com/turnstile"]')
        .forEach((script) => script.remove());
    delete window.turnstile;
    document.documentElement.className = '';
    vi.restoreAllMocks();
    vi.useRealTimers();
});

it.each(['light', 'dark'])('uses the panel %s theme and removes an already-loaded widget', async (theme) => {
    document.documentElement.className = theme;
    window.turnstile = api();
    await mount();
    expect(window.turnstile.render).toHaveBeenCalledWith(container.firstChild, expect.objectContaining({ theme }));
    await act(async () => root.render(null));
    expect(window.turnstile.remove).toHaveBeenCalledExactlyOnceWith('widget-0');
    expect(vi.getTimerCount()).toBe(0);
});

it.each(['new', 'existing'])('clears readiness polling when leaving a page with a %s pending script', async (kind) => {
    if (kind === 'existing') pendingScript();
    await mount();
    expect(vi.getTimerCount()).toBe(1);
    await act(async () => root.render(null));
    expect(vi.getTimerCount()).toBe(0);
    window.turnstile = api();
    await act(async () => vi.advanceTimersByTime(200));
    expect(window.turnstile.render).not.toHaveBeenCalled();
});

it.each(['new', 'existing'])('renders once after a %s script becomes ready and cleans up', async (kind) => {
    if (kind === 'existing') pendingScript();
    await mount();
    window.turnstile = api();
    await act(async () => vi.advanceTimersByTime(300));
    expect(window.turnstile.render).toHaveBeenCalledTimes(1);
    expect(document.querySelectorAll('script[src*="challenges.cloudflare.com/turnstile"]')).toHaveLength(1);
    expect(vi.getTimerCount()).toBe(0);
    await act(async () => root.render(null));
    expect(window.turnstile.remove).toHaveBeenCalledExactlyOnceWith('widget-0');
});

it('replaces widgets on theme and site-key changes, invalidates tokens and ignores stale callbacks', async () => {
    const render = vi
        .fn<NonNullable<Window['turnstile']>['render']>()
        .mockReturnValueOnce('old')
        .mockReturnValue('new');
    window.turnstile = { ...api(), render };
    await mount();
    const old = render.mock.calls[0]![1];
    await act(async () => old.callback('token'));
    expect(verify).toHaveBeenCalledExactlyOnceWith('token');
    await act(async () => {
        document.documentElement.className = 'dark';
        window.dispatchEvent(new Event('designifychange'));
    });
    expect(window.turnstile.remove).toHaveBeenCalledWith('old');
    expect(expire).toHaveBeenCalledTimes(1);
    expect(render).toHaveBeenLastCalledWith(container.firstChild, expect.objectContaining({ theme: 'dark' }));
    await act(async () => {
        old.callback('stale-token');
        old['expired-callback']?.();
        old['error-callback']?.('stale-error');
    });
    expect(verify).toHaveBeenCalledTimes(1);
    expect(expire).toHaveBeenCalledTimes(1);
    expect(error).not.toHaveBeenCalled();
    await mount('replacement-key');
    expect(render).toHaveBeenLastCalledWith(
        container.firstChild,
        expect.objectContaining({ sitekey: 'replacement-key' }),
    );
    expect(window.turnstile.remove).toHaveBeenCalledTimes(2);
});

it('uses current callbacks and preserves imperative reset without recreating the widget', async () => {
    const render = vi.fn<NonNullable<Window['turnstile']>['render']>().mockReturnValue('widget-0');
    window.turnstile = { ...api(), render };
    const ref = createRef<TurnstileHandle>();
    await act(async () => root.render(<Turnstile ref={ref} siteKey='key' onVerify={verify} />));
    await act(async () =>
        root.render(<Turnstile ref={ref} siteKey='key' onVerify={verify} onExpire={expire} onError={error} />),
    );
    const options = render.mock.calls[0]![1];
    await act(async () => {
        options.callback('fresh-token');
        options['expired-callback']?.();
        options['error-callback']?.('error-code');
        ref.current?.reset();
    });
    expect(render).toHaveBeenCalledTimes(1);
    expect(verify).toHaveBeenCalledWith('fresh-token');
    expect(expire).toHaveBeenCalledTimes(1);
    expect(error).toHaveBeenCalledWith('error-code');
    expect(window.turnstile.reset).toHaveBeenCalledWith('widget-0');
});

it('survives strict effect replay and navigation back to an auth page', async () => {
    window.turnstile = api();
    await act(async () =>
        root.render(
            <StrictMode>
                <Turnstile siteKey='key' onVerify={verify} />
            </StrictMode>,
        ),
    );
    expect(window.turnstile.render).toHaveBeenCalledTimes(2);
    expect(window.turnstile.remove).toHaveBeenCalledTimes(1);
    await act(async () => root.render(null));
    await mount();
    expect(window.turnstile.render).toHaveBeenCalledTimes(3);
    expect(window.turnstile.remove).toHaveBeenCalledTimes(2);
});
