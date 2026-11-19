import { useLayoutEffect, useState, type CSSProperties, type RefObject } from 'react';

export default function useArchiveMenuPosition(
    open: boolean,
    anchor: RefObject<HTMLDivElement | null>,
    menu: RefObject<HTMLDivElement | null>,
    placement: 'bottom' | 'side' = 'bottom',
) {
    const [position, setPosition] = useState<CSSProperties>();

    useLayoutEffect(() => {
        if (!open) return;
        const update = () => {
            if (!anchor.current || !menu.current) return;
            const gap = 8;
            const navigation = document.querySelector('[data-mobile-bottom-bar]');
            const navigationTop = navigation?.getBoundingClientRect().height
                ? Math.min(
                      navigation.getBoundingClientRect().top,
                      ...Array.from(navigation.querySelectorAll('button, a, button > span')).map(
                          (element) => element.getBoundingClientRect().top,
                      ),
                  )
                : window.innerHeight;
            const bottom = Math.min(window.innerHeight, navigationTop) - gap;
            const rect = anchor.current.getBoundingClientRect();
            const width = Math.min(192, window.innerWidth - gap * 2);
            const height = Math.min(
                menu.current.scrollHeight + menu.current.offsetHeight - menu.current.clientHeight,
                Math.max(0, bottom - gap),
            );
            const sideways = placement === 'side' && window.innerWidth >= 640;
            const left = sideways
                ? rect.right + width > window.innerWidth - gap
                    ? rect.left - width + gap
                    : rect.right - gap
                : rect.left;
            const top = sideways ? rect.top : rect.bottom + height <= bottom ? rect.bottom : rect.top - height;
            setPosition({
                position: 'fixed',
                left: Math.max(gap, Math.min(left, window.innerWidth - width - gap)),
                top: Math.max(gap, Math.min(top, bottom - height)),
                width,
                maxHeight: Math.max(0, bottom - gap),
                overflowY: 'auto',
            });
        };
        update();
        window.addEventListener('resize', update);
        window.addEventListener('scroll', update, true);
        const observer = new ResizeObserver(update);
        if (anchor.current) observer.observe(anchor.current);
        if (menu.current) observer.observe(menu.current);
        return () => {
            window.removeEventListener('resize', update);
            window.removeEventListener('scroll', update, true);
            observer.disconnect();
        };
    }, [open, anchor, menu, placement]);

    return position;
}
