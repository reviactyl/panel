import { useState, useEffect, useRef, useId } from 'react';
import { autoUpdate, flip, offset, shift, size, useFloating } from '@floating-ui/react';
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
    const listId = useId();
    const { refs, floatingStyles } = useFloating<HTMLButtonElement>({
        open: isOpen,
        placement: 'bottom-end',
        strategy: 'fixed',
        whileElementsMounted: autoUpdate,
        middleware: [
            offset(4),
            flip({ padding: 8 }),
            shift({ padding: 8 }),
            size({
                padding: 8,
                apply({ availableHeight, elements }) {
                    elements.floating.style.maxHeight = `${Math.max(0, Math.min(320, availableHeight))}px`;
                },
            }),
        ],
    });

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
        if (!isOpen) return;

        const handleClickOutside = (event: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                setIsOpen(false);
                refs.domReference.current?.focus();
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        document.addEventListener('keydown', handleKeyDown);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [isOpen, refs.domReference]);

    const handleSelect = async (code: string) => {
        setIsOpen(false);
        refs.domReference.current?.focus();
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
                ref={refs.setReference}
                type='button'
                aria-label={`${t('overview.language')}: ${currentLanguage?.name || selectedLang}`}
                aria-expanded={isOpen}
                aria-controls={isOpen ? listId : undefined}
                className='flex cursor-pointer items-center gap-2 rounded-ui border border-gray-800 bg-gray-900 px-3 py-2 text-sm text-gray-200 transition-all hover:border-gray-600 hover:bg-gray-700'
                onClick={() => setIsOpen(!isOpen)}
            >
                {currentLanguage?.flag ? (
                    <span
                        aria-hidden='true'
                        className={`fi fi-${currentLanguage.flag} inline-block h-3.75 w-5 rounded-xs shadow-[0_0_2px_rgba(0,0,0,0.3)]`}
                    />
                ) : (
                    <span>{selectedLang.toUpperCase()}</span>
                )}
            </button>

            {isOpen && (
                <div
                    id={listId}
                    ref={refs.setFloating}
                    role='group'
                    aria-label={t('overview.language')}
                    style={floatingStyles}
                    className='z-50 max-h-80 min-w-50 max-w-[calc(100vw-1rem)] overflow-y-auto overscroll-contain rounded-ui border border-gray-800 bg-gray-900/90 shadow-lg backdrop-blur-md'
                >
                    {Object.entries(languages).map(([code, info]) => (
                        <button
                            key={code}
                            type='button'
                            aria-pressed={code === selectedLang}
                            className={`flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition-colors hover:text-accent rtl:text-right ${
                                code === selectedLang
                                    ? 'bg-primary-500/10 text-accent'
                                    : 'bg-transparent text-gray-200 hover:bg-primary-500/10'
                            }`}
                            onClick={() => handleSelect(code)}
                        >
                            {info.flag && (
                                <span
                                    aria-hidden='true'
                                    className={`fi fi-${info.flag} inline-block h-3.75 w-5 rounded-xs shadow-[0_0_2px_rgba(0,0,0,0.3)]`}
                                />
                            )}
                            {info.name}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
};

export default NavbarLanguageSwitcher;
