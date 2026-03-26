import { Suspense, lazy, useMemo } from 'react';

interface Props {
    extensionId: string;
    modulePath: string;
    exportName?: string;
}

const normalizeModuleUrl = (extensionId: string, modulePath: string) => {
    const normalized = modulePath.replace(/^\/+/, '');

    return `/extensions/${extensionId}/${normalized}`;
};

export const ExtensionModule = ({ extensionId, modulePath, exportName }: Props) => {
    const LazyComponent = useMemo(
        () =>
            lazy(async () => {
                const moduleUrl = normalizeModuleUrl(extensionId, modulePath);
                let imported: any;

                try {
                    imported = await import(/* @vite-ignore */ moduleUrl);
                } catch (error) {
                    console.error('Failed to load extension module.', {
                        extensionId,
                        modulePath,
                        moduleUrl,
                        error,
                    });

                    return {
                        default: () => null,
                    };
                }

                const selected = exportName ? imported?.[exportName] : imported?.default;

                return {
                    default: selected ?? imported?.default ?? (() => null),
                };
            }),
        [extensionId, modulePath, exportName]
    );

    return (
        <Suspense fallback={null}>
            <LazyComponent />
        </Suspense>
    );
};
