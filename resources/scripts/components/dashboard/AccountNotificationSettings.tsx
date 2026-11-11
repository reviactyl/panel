import { useState } from 'react';
import useSWR from 'swr';
import { useTranslation } from 'react-i18next';
import http from '@/api/http';

interface NotificationCategory {
    category: string;
    database: boolean;
    email: boolean;
    emailAvailable: boolean;
}

interface NotificationSettings {
    categories: NotificationCategory[];
}

const endpoint = '/api/client/account/notifications';

const fetcher = async (): Promise<NotificationSettings> => (await http.get(endpoint)).data;

const inputClassName =
    'h-4 w-4 rounded border-gray-600 bg-gray-800 text-reviactyl focus:ring-2 focus:ring-reviactyl/60 focus:ring-offset-2 focus:ring-offset-gray-900';

const AccountNotificationSettings = () => {
    const { t } = useTranslation('notifications');
    const { data, error, mutate } = useSWR<NotificationSettings>(endpoint, fetcher);
    const [busy, setBusy] = useState<string | null>(null);
    const [updateError, setUpdateError] = useState(false);

    const updateCategory = async (category: NotificationCategory, channel: 'database' | 'email') => {
        const key = `${category.category}:${channel}`;
        const nextCategory = { ...category, [channel]: !category[channel] };
        if (!data || busy) return;

        setBusy(key);
        setUpdateError(false);
        const previous = data;
        await mutate(
            {
                ...data,
                categories: data.categories.map((item) => (item.category === category.category ? nextCategory : item)),
            },
            false,
        );
        try {
            await http.put(endpoint, {
                category: nextCategory.category,
                database: nextCategory.database,
                email: nextCategory.email,
            });
        } catch {
            await mutate(previous, false);
            setUpdateError(true);
        } finally {
            setBusy(null);
        }
    };

    if (error) {
        return (
            <p role='alert' className='text-sm text-danger'>
                {t('settings-error', 'Notification settings could not be loaded.')}
                <button type='button' className='ml-1 underline' onClick={() => void mutate()}>
                    {t('retry')}
                </button>
            </p>
        );
    }

    if (!data) {
        return (
            <p role='status' className='text-sm text-gray-400'>
                {t('settings-loading', 'Loading notification settings…')}
            </p>
        );
    }

    return (
        <div className='space-y-4'>
            {updateError && (
                <p role='alert' className='text-sm text-danger'>
                    {t('error')}{' '}
                    <button
                        type='button'
                        className='underline'
                        onClick={() => {
                            setUpdateError(false);
                            void mutate();
                        }}
                    >
                        {t('retry')}
                    </button>
                </p>
            )}

            <fieldset className='overflow-hidden rounded-ui border border-gray-800'>
                <legend className='sr-only'>{t('settings-type', 'Notification type')}</legend>
                <div className='grid grid-cols-[minmax(0,1fr)_2.5rem_2.5rem] items-center gap-2 border-b border-gray-800 bg-gray-950/40 px-3 py-2 text-xs text-gray-400'>
                    <span>{t('settings-type', 'Notification type')}</span>
                    <span className='text-center'>{t('settings-panel', 'Panel')}</span>
                    <span className='text-center'>{t('settings-email', 'Email')}</span>
                </div>
                {data.categories.map((category) => {
                    const title = t(`categories.${category.category}.title`, category.category);

                    return (
                        <div
                            key={category.category}
                            className='grid grid-cols-[minmax(0,1fr)_2.5rem_2.5rem] items-center gap-2 border-b border-gray-800 px-3 py-3 last:border-b-0'
                        >
                            <p className='min-w-0 text-sm font-medium text-gray-100'>{title}</p>
                            <label className='flex justify-center'>
                                <span className='sr-only'>
                                    {title} {t('settings-panel', 'Panel')}
                                </span>
                                <input
                                    type='checkbox'
                                    className={inputClassName}
                                    checked={category.database}
                                    disabled={!!busy}
                                    onChange={() => void updateCategory(category, 'database')}
                                />
                            </label>
                            <label className='flex justify-center'>
                                <span className='sr-only'>
                                    {title} {t('settings-email', 'Email')}
                                </span>
                                <input
                                    type='checkbox'
                                    className={inputClassName}
                                    checked={category.email}
                                    disabled={!category.emailAvailable || !!busy}
                                    onChange={() => void updateCategory(category, 'email')}
                                />
                            </label>
                        </div>
                    );
                })}
            </fieldset>
        </div>
    );
};

export default AccountNotificationSettings;
