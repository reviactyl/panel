import { type ReactNode, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { FaDesktop, FaMoon, FaSun } from 'react-icons/fa6';

type Theme = 'light' | 'dark' | 'system';

interface ThemeProps {
    children: ReactNode;
}

const getStoredTheme = (): Theme => {
    const stored = localStorage.getItem('theme');

    return stored === 'light' || stored === 'dark' || stored === 'system' ? stored : 'system';
};

const applyTheme = (theme: Theme) => {
    const root = document.documentElement;

    root.classList.remove('light', 'dark');

    if (theme === 'system') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.add(prefersDark ? 'dark' : 'light');
    } else {
        root.classList.add(theme);
    }
};

export const Theme = ({ children }: ThemeProps) => {
    const [theme, setTheme] = useState<Theme>('system');

    useEffect(() => {
        const storedTheme = getStoredTheme();

        setTheme(storedTheme);
        applyTheme(storedTheme);
    }, []);

    useEffect(() => {
        const handleThemeChange = () => {
            const storedTheme = getStoredTheme();

            setTheme(storedTheme);
            applyTheme(storedTheme);
        };

        window.addEventListener('themechange', handleThemeChange);

        return () => window.removeEventListener('themechange', handleThemeChange);
    }, []);

    useEffect(() => {
        if (theme !== 'system') return;

        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        const handleChange = () => applyTheme('system');

        mediaQuery.addEventListener('change', handleChange);

        return () => mediaQuery.removeEventListener('change', handleChange);
    }, [theme]);

    return <div>{children}</div>;
};

export const ThemeToggle = () => {
    const { t } = useTranslation('dashboard/account');
    const [theme, setTheme] = useState<Theme>('system');

    useEffect(() => {
        setTheme(getStoredTheme());
    }, []);

    const changeTheme = (value: Theme) => {
        setTheme(value);
        localStorage.setItem('theme', value);
        applyTheme(value);
        window.dispatchEvent(new Event('themechange'));
    };

    return (
        <div className='flex items-center justify-between mb-2'>
            <p className='flex-1'>{t('overview.theme-toggle')}</p>

            <div className='flex items-center rounded-ui border border-gray-700 overflow-hidden'>
                <button
                    type='button'
                    onClick={() => changeTheme('light')}
                    className={[
                        'px-3 py-1.5 text-sm transition-colors',
                        theme === 'light' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800',
                    ].join(' ')}
                >
                    <FaSun className='w-4 h-4 mr-1' />
                </button>

                <button
                    type='button'
                    onClick={() => changeTheme('dark')}
                    className={[
                        'px-3 py-1.5 text-sm transition-colors border-x border-gray-600',
                        theme === 'dark' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800',
                    ].join(' ')}
                >
                    <FaMoon className='w-4 h-4 mr-1' />
                </button>

                <button
                    type='button'
                    onClick={() => changeTheme('system')}
                    className={[
                        'px-3 py-1.5 text-sm transition-colors',
                        theme === 'system' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800',
                    ].join(' ')}
                >
                    <FaDesktop className='w-4 h-4 mr-1' />
                </button>
            </div>
        </div>
    );
};
