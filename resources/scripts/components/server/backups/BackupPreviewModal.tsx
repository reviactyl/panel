import { useEffect, useRef, useState } from 'react';
import { Dialog } from '@headlessui/react';
import { FaArrowLeft, FaFile, FaFolder, FaXmark } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import http, { httpErrorToHuman } from '@/api/http';
import { ServerBackup } from '@/api/server/types';
import { ServerContext } from '@/state/server';
import Spinner from '@/reviactyl/elements/Spinner';
import { bytesToString } from '@/lib/formatters';

interface Entry {
    name: string;
    is_directory: boolean;
    size: number | null;
    preview_status: 'text' | 'large' | 'binary' | null;
}
interface Preview {
    entries?: Entry[];
    status?: 'text' | 'large' | 'binary';
    contents?: string;
}

export default function BackupPreviewModal({ backup, onClose }: { backup: ServerBackup; onClose: () => void }) {
    const { t } = useTranslation('server/backups');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [location, setLocation] = useState({ path: '', file: false });
    const [result, setResult] = useState<Preview>();
    const [error, setError] = useState('');
    const [attempt, setAttempt] = useState(0);
    const cache = useRef(new Map<string, Preview>());
    const parts = location.path.split('/').filter(Boolean);

    useEffect(() => {
        const key = JSON.stringify(location);
        const cached = cache.current.get(key);
        if (cached) {
            setResult(cached);
            return;
        }
        const controller = new AbortController();
        setResult(undefined);
        setError('');
        http.get(`/api/client/servers/${uuid}/backups/${backup.uuid}/preview`, {
            params: { path: location.path, contents: location.file ? 1 : 0 },
            signal: controller.signal,
            timeout: 90000,
        })
            .then(({ data }) => {
                if (!controller.signal.aborted) {
                    if (cache.current.size >= 20) {
                        cache.current.delete(cache.current.keys().next().value!);
                    }
                    cache.current.set(key, data);
                    setResult(data);
                }
            })
            .catch((err) => {
                if (!controller.signal.aborted) setError(httpErrorToHuman(err));
            });
        return () => controller.abort();
    }, [uuid, backup.uuid, location, attempt]);

    const navigate = (path: string, file = false) => {
        setResult(undefined);
        setError('');
        setLocation({ path, file });
    };
    const parent = parts.slice(0, -1).join('/');
    const buttonClass = 'rounded px-2 py-1 hover:bg-gray-700 focus-visible:outline-2 focus-visible:outline-primary-400';

    return (
        <Dialog open onClose={onClose} className='relative z-50'>
            <div className='fixed inset-0 bg-gray-900/40 backdrop-blur-xs' aria-hidden='true' />
            <div className='fixed inset-0 flex items-center justify-center p-3 sm:p-6'>
                <Dialog.Panel className='flex max-h-[90dvh] w-full max-w-5xl flex-col overflow-hidden rounded-ui border border-gray-700 bg-gray-800 shadow-lg'>
                    <div className='flex items-start justify-between gap-4 border-b border-gray-700 p-4 sm:px-6'>
                        <Dialog.Title className='font-header text-xl font-medium text-gray-50'>
                            {t('preview-title')}
                        </Dialog.Title>
                        <button type='button' onClick={onClose} aria-label={t('preview-close')} className={buttonClass}>
                            <FaXmark className='h-5 w-5' aria-hidden='true' />
                        </button>
                    </div>
                    <nav
                        aria-label={t('preview-path')}
                        className='flex flex-wrap items-center gap-1 border-b border-gray-700 px-3 py-2 text-sm sm:px-5'
                    >
                        {parts.length > 0 && (
                            <button
                                type='button'
                                className={buttonClass}
                                onClick={() => navigate(parent)}
                                aria-label={t('preview-back')}
                            >
                                <FaArrowLeft aria-hidden='true' />
                            </button>
                        )}
                        <button type='button' className={buttonClass} onClick={() => navigate('')}>
                            /
                        </button>
                        {parts.map((part, index) => (
                            <span key={index} className='flex min-w-0 items-center gap-1'>
                                {index > 0 && (
                                    <span aria-hidden='true' className='text-gray-400'>
                                        /
                                    </span>
                                )}
                                {index === parts.length - 1 ? (
                                    <span className='break-all px-2 py-1 text-gray-100' aria-current='location'>
                                        {part}
                                    </span>
                                ) : (
                                    <button
                                        type='button'
                                        className={`${buttonClass} break-all`}
                                        onClick={() => navigate(parts.slice(0, index + 1).join('/'))}
                                    >
                                        {part}
                                    </button>
                                )}
                            </span>
                        ))}
                    </nav>
                    <div className='h-[60dvh] min-h-48 overflow-auto' aria-busy={!result && !error}>
                        {error ? (
                            <div role='alert' className='p-6'>
                                <p className='text-red-200'>{error}</p>
                                <button
                                    type='button'
                                    className={`${buttonClass} mt-4 text-sm`}
                                    onClick={() => setAttempt(attempt + 1)}
                                >
                                    {t('preview-retry')}
                                </button>
                            </div>
                        ) : !result ? (
                            <div
                                role='status'
                                className='flex h-full items-center justify-center gap-3 text-sm text-gray-300'
                            >
                                <Spinner size='small' />
                                {t('preview-loading')}
                            </div>
                        ) : location.file ? (
                            result.status === 'text' ? (
                                result.contents === '' ? (
                                    <p className='p-6 text-sm text-gray-300'>{t('preview-empty-file')}</p>
                                ) : (
                                    <pre
                                        tabIndex={0}
                                        aria-label={parts[parts.length - 1]}
                                        className='min-h-full w-max min-w-full p-4 font-mono text-sm leading-6 text-gray-100 outline-offset-[-2px] selection:bg-primary-600 sm:p-6'
                                    >
                                        {result.contents}
                                    </pre>
                                )
                            ) : (
                                <p className='p-6 text-sm text-gray-300'>
                                    {t(result.status === 'large' ? 'preview-large-file' : 'preview-binary')}
                                </p>
                            )
                        ) : result.entries?.length ? (
                            <ul aria-label={t('preview-files')} className='divide-y divide-gray-700/60'>
                                {result.entries.map((entry) => (
                                    <li key={entry.name}>
                                        <button
                                            type='button'
                                            disabled={!entry.is_directory && entry.preview_status !== 'text'}
                                            title={
                                                entry.preview_status === 'large'
                                                    ? t('preview-large-file')
                                                    : entry.preview_status === 'binary'
                                                      ? t('preview-binary')
                                                      : undefined
                                            }
                                            className='disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent flex w-full items-center gap-3 px-4 py-3 text-left text-sm hover:bg-gray-700 focus-visible:outline-2 focus-visible:outline-primary-400 focus-visible:outline-offset-[-2px] sm:px-6'
                                            onClick={() =>
                                                navigate(
                                                    [location.path, entry.name].filter(Boolean).join('/'),
                                                    !entry.is_directory,
                                                )
                                            }
                                        >
                                            {entry.is_directory ? (
                                                <FaFolder
                                                    aria-label={t('preview-folder')}
                                                    className='shrink-0 text-primary-300'
                                                />
                                            ) : (
                                                <FaFile aria-hidden='true' className='shrink-0 text-gray-400' />
                                            )}
                                            <span className='min-w-0 flex-1 break-all'>{entry.name}</span>
                                            {entry.size !== null && (
                                                <span className='shrink-0 tabular-nums text-gray-300'>
                                                    {bytesToString(entry.size)}
                                                </span>
                                            )}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className='p-6 text-sm text-gray-300'>{t('preview-empty-folder')}</p>
                        )}
                    </div>
                </Dialog.Panel>
            </div>
        </Dialog>
    );
}
