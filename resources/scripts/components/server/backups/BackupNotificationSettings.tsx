import { useSubuserPreview } from '@/context/SubuserPreviewContext';
import { useState } from 'react';
import useSWR from 'swr';
import { useTranslation } from 'react-i18next';
import { BellIcon } from '@heroicons/react/outline';
import http from '@/api/http';
import { ServerContext } from '@/state/server';

interface Preferences {
    manual: boolean;
    scheduled: boolean;
    email: boolean;
}
export default function BackupNotificationSettings() {
    const { t } = useTranslation('notifications');
    const { session } = useSubuserPreview();
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const url = `/api/client/servers/${uuid}/backups/notification-preferences`;
    const { data, error, mutate } = useSWR<Preferences>(session ? null : url, async () => (await http.get(url)).data);
    const [busy, setBusy] = useState(false);
    const [failed, setFailed] = useState(false);
    const [retryKey, setRetryKey] = useState<keyof Preferences | null>(null);
    const update = async (key: keyof Preferences) => {
        if (!data) return;
        setBusy(true);
        setFailed(false);
        try {
            const next = { ...data, [key]: !data[key] };
            await mutate(next, false);
            const result = await http.put(url, next);
            await mutate(result.data, false);
            setRetryKey(null);
        } catch {
            await mutate(data, false);
            setFailed(true);
            setRetryKey(key);
        } finally {
            setBusy(false);
        }
    };
    if (session) return null;
    return (
        <details className='mt-6 border-t border-gray-800 pt-4 text-sm text-gray-300'>
            <summary className='flex w-fit cursor-pointer items-center gap-2 rounded-ui focus-visible:outline-2 focus-visible:outline-reviactyl'>
                <BellIcon className='h-4 w-4' />
                {t('backup-settings')}
            </summary>
            {(failed || error) && (
                <p role='alert' className='mt-3 text-danger'>
                    {t('error')}{' '}
                    <button
                        type='button'
                        onClick={() => {
                            if (retryKey) void update(retryKey);
                            else void mutate();
                        }}
                        className='underline'
                    >
                        {t('retry')}
                    </button>
                </p>
            )}
            {!data && !error && (
                <p role='status' className='mt-3 text-gray-400'>
                    {t('loading')}
                </p>
            )}
            {data && (
                <div className='mt-4 flex flex-wrap gap-x-6 gap-y-3'>
                    {(['manual', 'scheduled', 'email'] as const).map((key) => (
                        <label key={key} className='flex cursor-pointer items-center gap-2'>
                            <input
                                type='checkbox'
                                checked={data[key]}
                                disabled={busy}
                                onChange={() => void update(key)}
                                className='rounded border-gray-600 bg-gray-800 text-reviactyl focus:ring-reviactyl'
                            />
                            {t(`backup-${key}`)}
                        </label>
                    ))}
                </div>
            )}
        </details>
    );
}
