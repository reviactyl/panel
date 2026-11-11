import { loadExtensionRuntime } from './loadExtensionRuntime';
import { Component, Suspense, lazy, useMemo, type ComponentType, type ReactNode } from 'react';
import { useExtensions } from '@/extensions/useExtensions';
import type { ExtensionLayoutDefinition, ExtensionLayoutName } from '@/extensions/types';

interface Context {
    eggId?: number;
    nestId?: number;
}

interface Props {
    name: ExtensionLayoutName;
    fallback: ReactNode;
    componentProps?: Record<string, unknown>;
    context?: Context;
    disabled?: boolean;
}

class LayoutBoundary extends Component<{ fallback: ReactNode; children: ReactNode }, { failed: boolean }> {
    override state = { failed: false };

    static getDerivedStateFromError() {
        return { failed: true };
    }

    override componentDidCatch(error: Error) {
        console.error('Failed to render extension layout.', error);
    }

    override render() {
        return this.state.failed ? this.props.fallback : this.props.children;
    }
}

const matchesContext = (layout: ExtensionLayoutDefinition, context?: Context) => {
    const eggId = layout.eggId ?? layout.egg_id;
    const nestId = layout.nestId ?? layout.nest_id;
    const eggIds = layout.eggIds ?? layout.egg_ids;
    const nestIds = layout.nestIds ?? layout.nest_ids;

    if (eggId === undefined && nestId === undefined && eggIds === undefined && nestIds === undefined) return true;

    return (
        (eggId !== undefined && eggId === context?.eggId) ||
        (nestId !== undefined && nestId === context?.nestId) ||
        (context?.eggId !== undefined && Array.isArray(eggIds) && eggIds.includes(context.eggId)) ||
        (context?.nestId !== undefined && Array.isArray(nestIds) && nestIds.includes(context.nestId))
    );
};

const LayoutModule = ({
    extensionId,
    layout,
    fallback,
    componentProps,
}: Pick<Props, 'fallback' | 'componentProps'> & { extensionId: string; layout: ExtensionLayoutDefinition }) => {
    const modulePath = layout.module;
    const exportName = layout.export;
    const LoadedLayout = useMemo(
        () =>
            lazy(async () => {
                const url = `/extensions/${extensionId}/${modulePath.replace(/^\/+/, '')}`;
                await loadExtensionRuntime();
                const imported = await import(/* @vite-ignore */ url);
                const selected = imported[exportName ?? 'default'];
                if (!selected) throw new Error(`Missing layout export: ${exportName ?? 'default'}`);
                return { default: selected as ComponentType<Record<string, unknown>> };
            }),
        [extensionId, modulePath, exportName],
    );

    return (
        <LayoutBoundary fallback={fallback}>
            <Suspense fallback={fallback}>
                <LoadedLayout {...componentProps} defaultLayout={fallback} />
            </Suspense>
        </LayoutBoundary>
    );
};

export const ExtensionLayout = ({ name, fallback, componentProps, context, disabled = false }: Props) => {
    const { data } = useExtensions();
    const candidates = (Array.isArray(data) ? data : []).flatMap((extension) =>
        (Array.isArray(extension.frontend?.layouts) ? extension.frontend.layouts : [])
            .filter(
                (layout) => layout?.name === name && typeof layout.module === 'string' && layout.module.trim() !== '',
            )
            .filter((layout) => matchesContext(layout, context))
            .map((layout) => ({ extensionId: extension.id, layout })),
    );
    candidates.sort(
        (a, b) => (a.layout.order ?? 0) - (b.layout.order ?? 0) || a.extensionId.localeCompare(b.extensionId),
    );
    const selected = candidates[0];

    if (disabled || !selected) return <>{fallback}</>;

    return (
        <LayoutModule
            key={`${selected.extensionId}:${selected.layout.module}:${selected.layout.export ?? ''}`}
            {...selected}
            fallback={fallback}
            componentProps={componentProps}
        />
    );
};
