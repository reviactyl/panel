let pending: Promise<unknown> | undefined;

export const loadExtensionRuntime = (): Promise<unknown> => {
    pending ??= import('./runtimeModules').catch((error) => {
        pending = undefined;
        throw error;
    });

    return pending;
};
