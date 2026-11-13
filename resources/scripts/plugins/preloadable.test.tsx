import { act, Suspense, useState } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { beforeEach, afterEach, expect, it } from 'vitest';
import preloadable from './preloadable';
let root: Root;
let container: HTMLDivElement;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
});
const Counter = () => {
    const [count, setCount] = useState(0);
    return <button onClick={() => setCount(count + 1)}>{count}</button>;
};
it('renders a fully prepared screen without ever committing its loading fallback', async () => {
    const Screen = preloadable(async () => ({ default: Counter }));
    await Screen.preload();
    let fallbackRenders = 0;
    const Fallback = () => {
        fallbackRenders++;
        return <p>Loading</p>;
    };
    await act(async () =>
        root.render(
            <Suspense fallback={<Fallback />}>
                <Screen />
            </Suspense>,
        ),
    );
    expect(container.textContent).toBe('0');
    expect(fallbackRenders).toBe(0);
});
it('reveals a newly loaded screen immediately and preserves its state on subsequent renders', async () => {
    let resolve!: (module: { default: typeof Counter }) => void;
    const Screen = preloadable(
        () =>
            new Promise((done) => {
                resolve = done;
            }),
    );
    const render = (revision: number) => (
        <div data-revision={revision}>
            <Suspense fallback={<p>Loading</p>}>
                <Screen />
            </Suspense>
        </div>
    );
    await act(async () => root.render(render(1)));
    expect(container.querySelector('.animate-spin')).not.toBeNull();
    await act(async () => resolve({ default: Counter }));
    await act(async () => container.querySelector('button')!.click());
    expect(container.textContent).toBe('1');
    await act(async () => root.render(render(2)));
    expect(container.textContent).toBe('1');
});
