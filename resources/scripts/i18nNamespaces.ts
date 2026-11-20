import namespaceMap from './i18nNamespaces.json';

export { namespaceMap };

export const namespacesForPath = (pathname: string): string[] => {
    if (pathname.startsWith('/auth')) return [...namespaceMap.auth];
    const namespaces = [
        ...namespaceMap.app,
        ...(pathname.startsWith('/server/') ? namespaceMap.server : namespaceMap.dashboard),
    ];
    if (pathname.startsWith('/server/')) {
        const screen = pathname.split('/')[3] || 'console';
        namespaces.push(...((namespaceMap.serverScreens as Record<string, string[]>)[screen] ?? []));
    }

    return namespaces;
};
