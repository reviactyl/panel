import { beforeEach, describe, expect, it, vi } from 'vitest';
const mocks = vi.hoisted(() => ({ get: vi.fn(), user: 'one', preview: '', session: new AbortController().signal }));
vi.mock('@/api/http', () => ({ default: { get: mocks.get }, getHttpSessionSignal: () => mocks.session }));
vi.mock('@/state', () => ({ store: { getState: () => ({ user: { data: { uuid: mocks.user } } }) } }));
vi.mock('@/lib/subuserPreviewStorage', () => ({ getPreviewToken: () => mocks.preview }));
import getServer, { prepareServer } from './getServer';
beforeEach(() => {
    vi.clearAllMocks();
    mocks.user = 'one';
    mocks.preview = '';
    mocks.session = new AbortController().signal;
    mocks.get.mockReturnValue(new Promise(() => {}));
});
describe('server entry preparation', () => {
    it('shares the click request with the route once, then refreshes on subsequent visits', () => {
        prepareServer('a');
        prepareServer('a');
        const first = getServer('a');
        expect(first).toBeInstanceOf(Promise);
        expect(mocks.get).toHaveBeenCalledTimes(1);
        void getServer('a');
        expect(mocks.get).toHaveBeenCalledTimes(2);
    });
    it.each(['user', 'session', 'preview', 'server', 'expired'])(
        'does not reuse a request across %s boundaries',
        (boundary) => {
            prepareServer('a');
            if (boundary === 'user') mocks.user = 'two';
            if (boundary === 'session') mocks.session = new AbortController().signal;
            if (boundary === 'preview') mocks.preview = 'new-preview';
            const clock = boundary === 'expired' ? vi.spyOn(Date, 'now').mockReturnValue(Date.now() + 3001) : undefined;
            void getServer(boundary === 'server' ? 'b' : 'a');
            expect(mocks.get).toHaveBeenCalledTimes(2);
            clock?.mockRestore();
        },
    );
    it('surfaces a failed preparation and allows the next request to retry', async () => {
        mocks.get.mockRejectedValueOnce(new Error('offline'));
        prepareServer('a');
        await expect(getServer('a')).rejects.toThrow('offline');
        void getServer('a');
        expect(mocks.get).toHaveBeenCalledTimes(2);
    });
});
