import { createElement, lazy, Suspense } from 'react';
import type { ComponentType, SVGProps } from 'react';

type ReviconComponent = ComponentType<SVGProps<SVGSVGElement>>;

interface ResolvedExtensionIcon {
    src?: string;
    component?: ReviconComponent;
}

const toPascalIconName = (value: string): string => {
    const base = value
        .trim()
        .replace(/\.svg$/i, '')
        .split(/[^a-zA-Z0-9]+/)
        .filter((segment) => segment.length > 0)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join('');

    if (!base) {
        return '';
    }

    return base.endsWith('Icon') ? base : `${base}Icon`;
};

const icons = import.meta.glob<ReviconComponent>('/node_modules/@revicons/react/solid/esm/Ri*.js', {
    import: 'default',
    exhaustive: true,
});
const resolvedIcons = new Map<string, ReviconComponent>();

const resolveReviconComponent = (iconRaw: string): ReviconComponent | undefined => {
    const raw = iconRaw.trim();
    if (!raw) {
        return undefined;
    }

    const iconNameRaw = /^icon:/i.test(raw) ? raw.replace(/^icon:/i, '') : raw;

    if (!/^[a-z0-9_-]+$/i.test(iconNameRaw)) {
        return undefined;
    }

    const pascalName = toPascalIconName(iconNameRaw);
    const candidates = [
        iconNameRaw,
        iconNameRaw.endsWith('Icon') ? iconNameRaw : `${iconNameRaw}Icon`,
        pascalName,
        pascalName.endsWith('Icon') ? pascalName : `${pascalName}Icon`,
    ];

    for (const candidate of candidates) {
        const load = icons[`/node_modules/@revicons/react/solid/esm/${candidate}.js`];
        if (load) {
            if (!resolvedIcons.has(candidate)) {
                const Icon = lazy(() =>
                    load()
                        .then((component) => ({ default: component }))
                        .catch(() => ({
                            default: (props: SVGProps<SVGSVGElement>) =>
                                createElement('svg', { ...props, 'aria-hidden': true }),
                        })),
                );
                resolvedIcons.set(candidate, (props) =>
                    createElement(
                        Suspense,
                        { fallback: createElement('svg', { ...props, 'aria-hidden': true }) },
                        createElement(Icon, props),
                    ),
                );
            }
            return resolvedIcons.get(candidate);
        }
    }

    return undefined;
};

export const resolveExtensionIcon = (icon?: string): ResolvedExtensionIcon => {
    const iconValue = (icon ?? '').trim();
    const reviconComponent = resolveReviconComponent(iconValue);
    if (reviconComponent) {
        return {
            component: reviconComponent,
        };
    }

    if (/^icon:/i.test(iconValue)) {
        return {};
    }

    if (iconValue.length > 0) {
        return {
            src: iconValue.startsWith('/') ? iconValue : `/${iconValue.replace(/^\/+/, '')}`,
        };
    }

    return {};
};
