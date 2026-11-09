import { useState, useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import i18n from '@/i18n';
import { useStoreState } from 'easy-peasy';
import useAccountPreference from '@/plugins/useAccountPreference';
import { ApplicationStore } from '@/state';
import 'flag-icons/css/flag-icons.min.css';

interface LanguageInfo {
    name: string;
    flag: string;
}

const NavbarLanguageSwitcher = () => {
    const { t } = useTranslation('dashboard/account');
    const user = useStoreState((state: ApplicationStore) => state.user.data);
    const serverLocale = useStoreState((state: any) => state.settings.data?.locale || 'en');
    const savePreference = useAccountPreference();
    const [languages, setLanguages] = useState<Record<string, LanguageInfo>>({});
    const [currentLang, setCurrentLang] = useState(
        user?.language && user.language !== 'geo' ? user.language : serverLocale,
    );
    const [pendingLang, setPendingLang] = useState<string | null>(null);
    const requestGeneration = useRef(0);
    const [isOpen, setIsOpen] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        fetch('/locales/list.json')
            .then((res) => res.json())
            .then((langs) => setLanguages(langs))
            .catch(() => setLanguages({ en: { name: 'English', flag: 'us' } }));

        const onLangChanged = (lng: string) => setCurrentLang(lng);
        i18n.on('languageChanged', onLangChanged);
        return () => i18n.off('languageChanged', onLangChanged);
    }, []);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleSelect = async (code: string) => {
        setIsOpen(false);
        const generation = ++requestGeneration.current;
        setPendingLang(code);

        if (user) {
            try {
                const saved = await savePreference('language', code);
                if (generation === requestGeneration.current) {
                    if (saved) setCurrentLang(code);
                    setPendingLang(null);
                }
            } catch (error) {
                if (generation === requestGeneration.current) setPendingLang(null);
                console.error('Failed to update language:', error);
            }
        } else {
            setPendingLang(null);
            setCurrentLang(code);
            i18n.changeLanguage(code);
        }
    };

    const selectedLang = pendingLang ?? currentLang;
    const currentLanguage = languages[selectedLang];

    return (
        <div className='relative' ref={containerRef}>
            <button
                type='button'
                aria-label={`${t('overview.language')}: ${currentLanguage?.name || selectedLang}`}
                aria-expanded={isOpen}
                className='flex cursor-pointer items-center gap-2 rounded-ui border border-gray-800 bg-gray-900 px-3 py-2 text-sm text-gray-200 transition-all hover:border-gray-600 hover:bg-gray-700'
                onClick={() => setIsOpen(!isOpen)}
            >
                {currentLanguage?.flag && (
                    <span
                        className={`fi fi-${currentLanguage.flag} inline-block h-3.75 w-5 rounded-xs shadow-[0_0_2px_rgba(0,0,0,0.3)]`}
                    />
                )}
            </button>

            <div
                className={`absolute right-0 top-full z-50 mt-1 min-w-50 overflow-hidden rounded-ui border border-gray-800 bg-gray-900/90 shadow-lg backdrop-blur-md rtl:left-0 rtl:right-auto ${
                    isOpen ? 'block' : 'hidden'
                }`}
            >
                {Object.entries(languages).map(([code, info]) => (
                    <button
                        key={code}
                        type='button'
                        className={`flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:text-reviactyl rtl:text-right ${
                            code === selectedLang
                                ? 'bg-[rgb(var(--color-primary)/0.2)] text-[rgb(var(--color-primary)/0.1)]'
                                : 'bg-transparent hover:bg-[rgb(var(--color-primary)/0.2)]'
                        }`}
                        onClick={() => handleSelect(code)}
                    >
                        {info.flag && (
                            <span
                                className={`fi fi-${info.flag} inline-block h-3.75 w-5 rounded-xs shadow-[0_0_2px_rgba(0,0,0,0.3)]`}
                            />
                        )}
                        {info.name}
                    </button>
                ))}
            </div>
        </div>
    );
};

export default NavbarLanguageSwitcher;
