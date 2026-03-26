import { useMemo } from 'react';
import type { ReactNode } from 'react';
import { ExtensionModule } from '@/extensions/ExtensionModule';
import { useExtensions } from '@/extensions/useExtensions';
import type { ExtensionRouteDefinition } from '@/extensions/types';

type RouteScope = 'dashboardRouter' | 'serverRouter';

interface GuardContext {
    eggId?: number;
    nestId?: number;
}

export interface InjectedRoute {
    path: string;
    element: ReactNode;
    permission?: string | string[];
    icon?: string;
}

const routeAllowed = (route: ExtensionRouteDefinition, context?: GuardContext): boolean => {
    const eggId = context?.eggId;
    const nestId = context?.nestId;

    const routeEggId = route.eggId ?? route.egg_id;
    const routeNestId = route.nestId ?? route.nest_id;
    const routeEggIds = route.eggIds ?? route.egg_ids;
    const routeNestIds = route.nestIds ?? route.nest_ids;

    const hasGuards =
        routeEggId !== undefined ||
        routeNestId !== undefined ||
        Array.isArray(routeEggIds) ||
        Array.isArray(routeNestIds);

    if (!hasGuards) {
        return true;
    }

    if (routeEggId !== undefined && routeEggId === eggId) {
        return true;
    }

    if (routeNestId !== undefined && routeNestId === nestId) {
        return true;
    }

    if (Array.isArray(routeEggIds) && eggId !== undefined && routeEggIds.includes(eggId)) {
        return true;
    }

    if (Array.isArray(routeNestIds) && nestId !== undefined && routeNestIds.includes(nestId)) {
        return true;
    }

    return false;
};

export const useExtensionRoutes = (scope: RouteScope, context?: GuardContext): InjectedRoute[] => {
    const { data } = useExtensions();
    const extensions = Array.isArray(data) ? data : [];

    return useMemo(() => {
        return extensions.flatMap((extension) => {
            const routes = extension.frontend?.routes?.[scope] ?? [];

            return routes
                .filter((route) => routeAllowed(route, context))
                .map((route) => ({
                    path: route.path,
                    permission: route.permission,
                    icon: route.icon,
                    element: (
                        <ExtensionModule
                            extensionId={extension.id}
                            modulePath={route.module}
                            exportName={route.export}
                        />
                    ),
                }));
        });
    }, [extensions, scope, context?.eggId, context?.nestId]);
};
