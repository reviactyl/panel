import { createElement, useEffect, useState, type ComponentType } from 'react';
import Spinner from '@/reviactyl/elements/Spinner';

export default function preloadable(load: () => Promise<{ default: ComponentType }>): ComponentType & {
    preload: () => Promise<{ default: ComponentType }>;
} {
    let resolved: ComponentType | undefined;
    let pending: ReturnType<typeof load> | undefined;
    const preload = () => {
        pending ??= load()
            .then((module) => {
                resolved = module.default;
                return module;
            })
            .catch((error) => {
                pending = undefined;
                throw error;
            });
        return pending;
    };
    const Prepared = () => {
        const [component, setComponent] = useState(() => resolved);
        const [failure, setFailure] = useState<{ error: unknown }>();
        useEffect(() => {
            let active = true;
            if (!component) {
                void preload().then(
                    (module) => active && setComponent(() => module.default),
                    (error) => active && setFailure({ error }),
                );
            }
            return () => {
                active = false;
            };
        }, []);
        if (failure) throw failure.error;
        return component ? createElement(component) : createElement(Spinner, { centered: true, size: 'large' });
    };
    return Object.assign(Prepared, { preload });
}
