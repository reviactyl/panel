import React, { useEffect, useState } from 'react';
import { XIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

const paletteKeys = ['Theme1', 'Theme2', 'Theme3', 'Theme4', 'Theme5', 'Theme6', 'Theme7'] as const;

type PaletteKey = typeof paletteKeys[number];

type ThemeData = {
    displayName: string;
    primary: string;
    50: string;
    100: string;
    200: string;
    300: string;
    400: string;
    500: string;
    600: string;
    700: string;
    800: string;
    900: string;
};


const getCookie = (name: string): string | null => {
    if (typeof document === 'undefined') return null;
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
};

const setCookie = (name: string, value: string, days = 30) => {
    if (typeof document === 'undefined') return;
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
};

const deleteCookie = (name: string) => {
    if (typeof document === 'undefined') return;
    document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
};

const hexToRgbString = (hex: string) => {
    if (!/^#?([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(hex)) return '0 0 0';
    let cleanHex = hex.replace('#', '');
    if (cleanHex.length === 3) {
        cleanHex = cleanHex
            .split('')
            .map((x) => x + x)
            .join('');
    }
    const bigint = parseInt(cleanHex, 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `${r} ${g} ${b}`;
};

const getThemeFromConfig = (key: PaletteKey): ThemeData => {
    const conf = typeof window !== 'undefined' ? window.ReviactylConfiguration || {} : {};
    const t = conf[key.toLowerCase()] || {};
    return {
        displayName: t.name || key,
        primary: t.colorPrimary,
        50: t.color50,
        100: t.color100,
        200: t.color200,
        300: t.color300,
        400: t.color400,
        500: t.color500,
        600: t.color600,
        700: t.color700,
        800: t.color800,
        900: t.color900,
    };
};

const applyTheme = (colors: ThemeData) => {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;
    Object.entries(colors).forEach(([key, hex]) => {
        root.style.setProperty(`--color-${key}`, hexToRgbString(hex));
    });
};

const clearTheme = () => {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;
    paletteKeys.forEach((key) => {
        Object.keys(getThemeFromConfig(key)).forEach((k) => {
            root.style.removeProperty(`--color-${k}`);
        });
    });
};

const ThemeSelector = () => {
    const { t } = useTranslation('dashboard/account');
    const [selected, setSelected] = useState<'default' | PaletteKey>('default');
    const [themes, setThemes] = useState<Record<PaletteKey, ThemeData> | null>(null);

    useEffect(() => {
        if (typeof window === 'undefined') return;

        const loadedThemes: Record<PaletteKey, ThemeData> = {} as any;
        for (const key of paletteKeys) {
            loadedThemes[key] = getThemeFromConfig(key);
        }
        setThemes(loadedThemes);

        const saved = getCookie('theme');
        if (saved && paletteKeys.includes(saved as PaletteKey)) {
            applyTheme(loadedThemes[saved as PaletteKey]);
            setSelected(saved as PaletteKey);
        } else {
            clearTheme();
            setSelected('default');
        }
    }, []);

    const handleThemeChange = (theme: 'default' | PaletteKey) => {
        if (!themes) return;

        setSelected(theme);
        if (theme === 'default') {
            clearTheme();
            deleteCookie('theme');
        } else {
            applyTheme(themes[theme]);
            setCookie('theme', theme);
        }
    };

    if (!themes) return null;

    return (
        <div className='px-4 py-2'>
            <div className='flex gap-3'>
                <button
                    onClick={() => handleThemeChange('default')}
                    className={`w-10 h-10 flex items-center justify-center rounded-full border text-sm hover:bg-gray-300 dark:hover:bg-gray-700 ${selected === 'default' ? 'ring-2 ring-reviactyl' : ''
                        }`}
                    title={t('theme-selector.default')}
                >
                    <XIcon className='h-8 w-8 text-danger/50' />
                </button>

                {paletteKeys.map((name) => {
                    const theme = themes[name];
                    const gradient = `conic-gradient(at top left, ${theme.primary}, ${theme[600]}, ${theme[800]})`;

                    return (
                        <button
                            key={name}
                            onClick={() => handleThemeChange(name)}
                            className={`w-10 h-10 rounded-full border shadow-sm transition ${selected === name ? 'ring-2 ring-reviactyl' : ''
                                }`}
                            style={{ background: gradient }}
                            title={theme.displayName}
                        />
                    );
                })}
            </div>
        </div>
    );
};

export default ThemeSelector;

export const ThemeLoader = () => {
    useEffect(() => {
        if (typeof window === 'undefined') return;

        const themeName = getCookie('theme');
        if (themeName && paletteKeys.includes(themeName as PaletteKey)) {
            const theme = getThemeFromConfig(themeName as PaletteKey);
            applyTheme(theme);
        }
    }, []);

    return null;
};
