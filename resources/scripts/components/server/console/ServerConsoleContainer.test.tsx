import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { rawDataToServerObject } from '@/api/server/getServer';
import type { ExtensionSlotDefinition } from '@/extensions/types';
import { ServerContext } from '@/state/server';
import ServerConsoleContainer from './ServerConsoleContainer';

const mocks = vi.hoisted(() => ({ slots: [] as ExtensionSlotDefinition[] }));

vi.mock('@/extensions/useExtensions', () => ({
    useExtensions: () => ({ data: [{ id: 'console-test', frontend: { slots: mocks.slots } }] }),
}));
vi.mock('@/extensions/ExtensionModule', () => ({
    ExtensionModule: ({ modulePath }: { modulePath: string }) => <output>{modulePath}</output>,
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => <main>{children}</main>,
}));
vi.mock('@/reviactyl/elements/Spinner', () => ({
    default: { Suspense: ({ children }: { children: ReactNode }) => <>{children}</> },
}));
vi.mock('@feature/Features', () => ({ default: () => null }));
vi.mock('./ConsoleBlock', () => ({ default: () => <div>console</div> }));
vi.mock('./StatGraphs', () => ({ default: () => <div>graphs</div> }));
vi.mock('./ServerDetailsBlock', () => ({ default: () => <div>details</div> }));

let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof ServerContext.useStore>;

const Console = () => {
    store = ServerContext.useStore();
    const server = ServerContext.useStoreState((state) => state.server.data);
    return server ? <ServerConsoleContainer /> : null;
};

const changeServer = async (eggId: number, nestId: number) => {
    await act(async () =>
        store.getActions().server.setServer(
            rawDataToServerObject({
                object: 'server',
                attributes: { egg_id: eggId, nest_id: nestId, status: null, sftp_details: {}, limits: {} },
            }),
        ),
    );
};

const visibleModules = () => Array.from(container.querySelectorAll('output'), (element) => element.textContent);
const placements = ['server:console:above', 'server:console:below'];

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.slots = [];
    container = document.createElement('div');
    root = createRoot(container);
    await act(async () =>
        root.render(
            <ServerContext.Provider>
                <Console />
            </ServerContext.Provider>,
        ),
    );
});

afterEach(async () => {
    await act(async () => root.unmount());
});

describe('console extension slot context', () => {
    it.each([
        { guard: { egg_id: 11 }, otherEgg: 12, otherNest: 22 },
        { guard: { egg_ids: [11, 13] }, otherEgg: 12, otherNest: 22 },
        { guard: { eggId: 11 }, otherEgg: 12, otherNest: 22 },
        { guard: { eggIds: [11, 13] }, otherEgg: 12, otherNest: 22 },
        { guard: { nest_id: 22 }, otherEgg: 11, otherNest: 23 },
        { guard: { nest_ids: [22, 24] }, otherEgg: 11, otherNest: 23 },
    ])('updates both placements for matching and nonmatching $guard', async ({ guard, otherEgg, otherNest }) => {
        mocks.slots = placements.flatMap((name) => [
            { name, module: `${name}:restricted`, ...guard },
            { name, module: `${name}:unrestricted` },
        ]);

        await changeServer(11, 22);
        expect(visibleModules()).toEqual(placements.flatMap((name) => [`${name}:restricted`, `${name}:unrestricted`]));

        await changeServer(otherEgg, otherNest);
        expect(visibleModules()).toEqual(placements.map((name) => `${name}:unrestricted`));
        expect(container.textContent).toContain('consoledetailsgraphs');

        await changeServer(11, 22);
        expect(visibleModules()).toEqual(placements.flatMap((name) => [`${name}:restricted`, `${name}:unrestricted`]));
    });

    it('continues to require permissions for matching restricted slots', async () => {
        mocks.slots = placements.map((name) => ({
            name,
            module: `${name}:permitted`,
            egg_id: 11,
            permission: 'backup.read',
        }));
        await changeServer(11, 22);
        expect(visibleModules()).toEqual([]);

        await act(async () => store.getActions().server.setPermissions(['backup.read']));
        expect(visibleModules()).toEqual(placements.map((name) => `${name}:permitted`));

        await changeServer(12, 22);
        expect(visibleModules()).toEqual([]);
    });
});
