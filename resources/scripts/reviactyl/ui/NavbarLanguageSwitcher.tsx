import { useState, useEffect, useRef } from 'react';
import i18n from '@/i18n';
import { useStoreActions, useStoreState } from 'easy-peasy';
import updateAccountLanguage from '@/api/account/updateAccountLanguage';
import { ApplicationStore } from '@/state';
import 'flag-icons/css/flag-icons.min.css';

interface LanguageInfo {
    name: string;
    flag: string;
}

const NavbarLanguageSwitcher = () => {
    const user = useStoreState((state: ApplicationStore) => state.user.data);
    const serverLocale = useStoreState((state: any) => state.settings.data?.locale || 'en');
    const setUserData = useStoreActions((actions: any) => actions.user.setUserData);
    const [languages, setLanguages] = useState<Record<string, LanguageInfo>>({});
    const [currentLang, setCurrentLang] = useState(
        user?.language && user.language !== 'geo' ? user.language : serverLocale,
    );
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
        setCurrentLang(code);
        setIsOpen(false);
        i18n.changeLanguage(code);

        if (user) {
            try {
                await updateAccountLanguage({ language: code });
                setUserData({ ...user, language: code });
            } catch (error) {
                console.error('Failed to update language:', error);
            }
        }
    };

    const currentLanguage = languages[currentLang];

    return (
        <div className='relative' ref={containerRef}>
            <button
                className='flex cursor-pointer items-center gap-2 rounded-ui border border-gray-800 bg-gray-900 px-3 py-2 text-sm text-gray-200 transition-all hover:border-gray-600 hover:bg-gray-700'
                onClick={() => setIsOpen(!isOpen)}
            >
                {currentLanguage?.flag && (
                    <span
                        className={`fi fi-${currentLanguage.flag} inline-block h-[15px] w-5 rounded-[2px] shadow-[0_0_2px_rgba(0,0,0,0.3)]`}
                    />
                )}
            </button>

            <div
                className={`absolute right-0 top-full z-50 mt-1 min-w-[200px] overflow-hidden rounded-ui border border-gray-800 bg-gray-900/90 shadow-lg backdrop-blur-md rtl:left-0 rtl:right-auto ${
                    isOpen ? 'block' : 'hidden'
                }`}
            >
                {Object.entries(languages).map(([code, info]) => (
                    <button
                        key={code}
                        className={`flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:text-reviactyl rtl:text-right ${
                            code === currentLang
                                ? 'bg-[rgb(var(--color-primary)/0.2)] text-[rgb(var(--color-primary)/0.1)]'
                                : 'bg-transparent hover:bg-[rgb(var(--color-primary)/0.2)]'
                        }`}
                        onClick={() => handleSelect(code)}
                    >
                        {info.flag && (
                            <span
                                className={`fi fi-${info.flag} inline-block h-[15px] w-5 rounded-[2px] shadow-[0_0_2px_rgba(0,0,0,0.3)]`}
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
