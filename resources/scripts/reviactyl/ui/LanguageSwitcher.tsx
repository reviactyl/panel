import React, { useState, useEffect } from 'react';
import i18n from '@/i18n';
import Select from '@/components/elements/Select';
import { useTranslation } from 'react-i18next';
import { useStoreActions, useStoreState } from 'easy-peasy';
import updateAccountLanguage from '@/api/account/updateAccountLanguage';
import { ApplicationStore } from '@/state';

interface LanguageInfo {
    name: string;
    flag: string;
}

const LanguageSwitcher: React.FC = () => {
    const { t } = useTranslation('dashboard/account');
    const user = useStoreState((state: ApplicationStore) => state.user.data);
    const setUserData = useStoreActions((actions: any) => actions.user.setUserData);
    const [languages, setLanguages] = useState<Record<string, LanguageInfo>>({});
    const [currentLang, setCurrentLang] = useState(user?.language || i18n.language);

    useEffect(() => {
        fetch('/locales/list.json')
            .then((res) => res.json())
            .then((langs) => setLanguages(langs))
            .catch(() => setLanguages({ en: { name: 'English', flag: 'us' } }));

        const onLangChanged = (lng: string) => setCurrentLang(lng);
        i18n.on('languageChanged', onLangChanged);
        return () => i18n.off('languageChanged', onLangChanged);
    }, []);

    const handleChange = async (e: React.ChangeEvent<HTMLSelectElement>) => {
        const newLang = e.target.value;
        setCurrentLang(newLang);
        i18n.changeLanguage(newLang);

        if (user) {
            try {
                await updateAccountLanguage({ language: newLang });
                setUserData({ ...user, language: newLang });
            } catch (error) {
                console.error('Failed to update language:', error);
            }
        }
    };

    return (
        <div className='flex flex-col gap-2 mb-2 sm:flex-row sm:justify-between sm:items-center'>
            <p className='min-w-0 flex-1'>{t('overview.language')}</p>
            <Select className='!pr-15 w-full min-w-0 sm:!w-auto' value={currentLang} onChange={handleChange}>
                {Object.entries(languages).map(([code, info]) => (
                    <option key={code} value={code}>
                        {info.name}
                    </option>
                ))}
            </Select>
        </div>
    );
};

export default LanguageSwitcher;

export const LocaleLoader: React.FC = () => {
    const userLanguage = useStoreState((state: any) => state.user.data?.language || 'en');

    useEffect(() => {
        if (userLanguage && userLanguage !== i18n.language) {
            i18n.changeLanguage(userLanguage);
        }
    }, [userLanguage]);

    return null;
};
