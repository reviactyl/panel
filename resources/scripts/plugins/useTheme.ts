import { useEffect, useState } from 'react';

export default function useTheme() {
    const [dark, setDark] = useState(() => document.documentElement.classList.contains('dark'));
    const [revision, setRevision] = useState(0);

    useEffect(() => {
        const update = () => {
            setDark(document.documentElement.classList.contains('dark'));
            setRevision((revision) => revision + 1);
        };
        const observer = new MutationObserver(update);
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        window.addEventListener('designifychange', update);

        return () => {
            observer.disconnect();
            window.removeEventListener('designifychange', update);
        };
    }, []);

    return { dark, revision };
}
