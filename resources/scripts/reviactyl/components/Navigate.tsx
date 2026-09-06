import { NavLink, type NavLinkProps, useLocation } from 'react-router-dom';

const stripTrailingSlash = (p: string) => (p.length > 1 ? p.replace(/\/+$/, '') : p);

export default function Navigate(props: NavLinkProps) {
    const location = useLocation();

    const toPath = typeof props.to === 'string' ? props.to : (props.to.pathname ?? '');
    const isSlashSafeActive = stripTrailingSlash(location.pathname) === stripTrailingSlash(toPath);

    const baseClassName = props.className;

    return (
        <NavLink
            {...props}
            className={({ isActive, isPending, isTransitioning }) => {
                const nextIsActive = isActive || isSlashSafeActive;

                const base =
                    typeof baseClassName === 'function'
                        ? baseClassName({ isActive: nextIsActive, isPending, isTransitioning })
                        : (baseClassName ?? '');

                return `${base} ${nextIsActive ? 'active' : ''}`.trim();
            }}
        />
    );
}
