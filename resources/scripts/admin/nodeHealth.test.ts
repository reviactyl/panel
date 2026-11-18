import { readFileSync } from 'node:fs';
import { afterEach, describe, expect, it, vi } from 'vitest';

interface HealthComponent {
    status: string;
    tooltip: string;
    requestController: AbortController | null;
    destroy?: () => void;
    [key: string]: unknown;
}

const alpineSource = readFileSync('vendor/livewire/livewire/dist/livewire.js', 'utf8');
const compilerSource = alpineSource.match(/ {2}function generateFunctionFromString[\s\S]*?\n {2}}\n/)![0];
const compile = new Function('evaluatorMemo', 'handleError', `${compilerSource}; return generateFunctionFromString`)(
    {},
    (error: Error) => {
        throw error;
    },
) as (expression: string) => (self: object, scope: object) => Promise<unknown>;

function mount(view: string) {
    const source = readFileSync(`resources/views/filament/${view}.blade.php`, 'utf8');
    const attribute = (name: string) => source.match(new RegExp(`${name}="([\\s\\S]*?)"`))![1]!;
    const component = new Function(`return (${attribute('x-data')})`)() as HealthComponent;
    Object.assign(component, {
        $el: { dataset: { url: '/api/system', token: 'test', latestAgent: '26.11.0' } },
        $watch: vi.fn(),
    });
    void compile(attribute('x-init'))({}, component);
    return component;
}

function response(version = '26.11.0') {
    return { ok: true, json: async () => ({ version }) };
}

async function settle() {
    await Promise.resolve();
    await Promise.resolve();
    await Promise.resolve();
}

afterEach(() => {
    vi.useRealTimers();
    vi.unstubAllGlobals();
});

describe.each(['columns/node-health', 'components/wings-warning'])('%s lifecycle', (view) => {
    it.each(['26.11.0', 'develop'])('polls %s while mounted, then cancels the next check', async (version) => {
        vi.useFakeTimers();
        const fetch = vi.fn().mockResolvedValue(response(version));
        vi.stubGlobal('fetch', fetch);
        const component = mount(view);
        await settle();
        expect(component.status).toBe('up');
        await vi.advanceTimersByTimeAsync(5000);
        expect(fetch).toHaveBeenCalledTimes(2);
        component.destroy?.();
        await vi.advanceTimersByTimeAsync(15000);
        expect(fetch).toHaveBeenCalledTimes(2);
    });

    it('aborts an in-flight fetch without showing a failure or restarting the timer', async () => {
        vi.useFakeTimers();
        let signal: AbortSignal;
        const fetch = vi.fn((_url, options) => {
            signal = options.signal;
            return new Promise((_resolve, reject) => {
                signal.addEventListener('abort', () => reject(new DOMException('Aborted', 'AbortError')));
            });
        });
        vi.stubGlobal('fetch', fetch);
        const component = mount(view);
        component.destroy?.();
        await settle();
        expect(signal!.aborted).toBe(true);
        expect(component.status).toBe('loading');
        await vi.advanceTimersByTimeAsync(15000);
        expect(fetch).toHaveBeenCalledTimes(1);
    });

    it('ignores a response body completing after teardown', async () => {
        vi.useFakeTimers();
        let finish!: (body: { version: string }) => void;
        const body = new Promise((resolve) => {
            finish = resolve;
        });
        const fetch = vi.fn().mockResolvedValue({ ok: true, json: () => body });
        vi.stubGlobal('fetch', fetch);
        const component = mount(view);
        await settle();
        component.destroy?.();
        finish({ version: '26.11.0' });
        await settle();
        expect(component.status).toBe('loading');
        expect(component.tooltip).toBe('');
        await vi.advanceTimersByTimeAsync(15000);
        expect(fetch).toHaveBeenCalledTimes(1);
    });

    it('reports network errors and retries while mounted', async () => {
        vi.useFakeTimers();
        const fetch = vi.fn().mockRejectedValue(new Error('Offline'));
        vi.stubGlobal('fetch', fetch);
        const component = mount(view);
        await settle();
        expect(component.status).toBe('down');
        expect(component.tooltip).toContain('Offline');
        await vi.advanceTimersByTimeAsync(5000);
        expect(fetch).toHaveBeenCalledTimes(2);
        component.destroy?.();
    });
});
