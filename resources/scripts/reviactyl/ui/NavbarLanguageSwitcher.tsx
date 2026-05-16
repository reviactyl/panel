import { useState, useEffect, useRef } from 'react';
import i18n from '@/i18n';
import { useStoreActions, useStoreState } from 'easy-peasy';
import updateAccountLanguage from '@/api/account/updateAccountLanguage';
import { ApplicationStore } from '@/state';
import styled from 'styled-components';
import tw from 'twin.macro';
import 'flag-icons/css/flag-icons.min.css';

interface LanguageInfo {
    name: string;
    flag: string;
}

const Container = styled.div`
    ${tw`relative`};
`;

const DropdownButton = styled.button`
    ${tw`flex items-center gap-2 px-3 py-2 bg-gray-900 border border-gray-800 rounded-ui text-gray-200 text-sm cursor-pointer transition-all`};

    &:hover {
        ${tw`border-gray-600 bg-gray-700`};
    }
`;

const DropdownMenu = styled.div<{ $isOpen: boolean }>`
    ${tw`absolute right-0 top-full mt-1 border border-gray-800 rounded-ui shadow-lg z-50 overflow-hidden min-w-[200px]`};
    display: ${(props) => (props.$isOpen ? 'block' : 'none')};

    [dir='rtl'] & {
        right: auto;
        left: 0;
    }
`;

const MenuItem = styled.button<{ $isActive?: boolean }>`
    ${tw`flex items-center gap-2 w-full px-3 py-2 text-left text-sm transition-colors hover:text-reviactyl`};

    [dir='rtl'] & {
        text-align: right;
    }

    ${(props) =>
        props.$isActive
            ? `
                color: rgb(var(--color-primary) / 10);
                background-color: rgb(var(--color-primary) / 0.2);
              `
            : `
                background-color: transparent;

                &:hover {
                    background-color: rgb(var(--color-primary) / 0.2);
                }
              `}
`;

const FlagIcon = styled.span`
    ${tw`inline-block`};
    width: 20px;
    height: 15px;
    border-radius: 2px;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
`;

const NavbarLanguageSwitcher = () => {
    const user = useStoreState((state: ApplicationStore) => state.user.data);
    const serverLocale = useStoreState((state: any) => state.settings.data?.locale || 'en');
    const setUserData = useStoreActions((actions: any) => actions.user.setUserData);
    const [languages, setLanguages] = useState<Record<string, LanguageInfo>>({});
    const [currentLang, setCurrentLang] = useState(
        user?.language && user.language !== 'geo' ? user.language : serverLocale
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
        <Container ref={containerRef}>
            <DropdownButton onClick={() => setIsOpen(!isOpen)}>
                {currentLanguage?.flag && <FlagIcon className={`fi fi-${currentLanguage.flag}`} />}
            </DropdownButton>

            <DropdownMenu className='bg-gray-900/90 backdrop-blur-md' $isOpen={isOpen}>
                {Object.entries(languages).map(([code, info]) => (
                    <MenuItem key={code} $isActive={code === currentLang} onClick={() => handleSelect(code)}>
                        {info.flag && <FlagIcon className={`fi fi-${info.flag}`} />}
                        {info.name}
                    </MenuItem>
                ))}
            </DropdownMenu>
        </Container>
    );
};

export default NavbarLanguageSwitcher;
