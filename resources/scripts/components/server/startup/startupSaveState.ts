interface StartupSaveState {
    revision: number;
    pendingSave: Promise<void>;
    getSnapshot: () => boolean;
    subscribe: (listener: () => void) => () => void;
    start: () => number;
    finish: (revision: number) => void;
}

const saves = new Map<string, StartupSaveState>();
const pendingSaves = new Set<StartupSaveState>();

const onBeforeUnload = (event: BeforeUnloadEvent) => {
    event.preventDefault();
    event.returnValue = '';
};

export default (uuid: string, variable: string): StartupSaveState => {
    const key = JSON.stringify([uuid, variable]);
    const existing = saves.get(key);
    if (existing) return existing;

    let pending = false;
    const listeners = new Set<() => void>();
    const cleanup = (current: StartupSaveState) => {
        queueMicrotask(() => {
            if (!pending && !listeners.size && saves.get(key) === current) saves.delete(key);
        });
    };
    const setPending = (current: StartupSaveState, value: boolean) => {
        pending = value;
        if (pending) {
            pendingSaves.add(current);
            window.addEventListener('beforeunload', onBeforeUnload);
        } else {
            pendingSaves.delete(current);
            if (!pendingSaves.size) window.removeEventListener('beforeunload', onBeforeUnload);
        }
        listeners.forEach((listener) => listener());
        cleanup(current);
    };
    const state: StartupSaveState = {
        revision: 0,
        pendingSave: Promise.resolve(),
        getSnapshot: () => pending,
        subscribe: (listener) => {
            listeners.add(listener);
            return () => {
                listeners.delete(listener);
                cleanup(state);
            };
        },
        start: () => {
            state.revision += 1;
            setPending(state, true);
            return state.revision;
        },
        finish: (revision) => {
            if (revision === state.revision) setPending(state, false);
        },
    };
    saves.set(key, state);
    return state;
};
