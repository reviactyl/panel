import { useState } from 'react';
import { Dialog, DialogBackdrop, DialogPanel, DialogTitle } from '@headlessui/react';
import {
    BellIcon,
    CheckIcon,
    CheckCircleIcon,
    XIcon,
    TrashIcon,
    ArrowRightIcon,
    ExclamationCircleIcon,
    ServerIcon,
    ArchiveIcon,
} from '@heroicons/react/outline';
import { Link } from 'react-router-dom';
import useSWR from 'swr';
import { useTranslation } from 'react-i18next';
import http from '@/api/http';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';

interface Notice {
    id: string;
    data: {
        event: string;
        server?: string;
        body?: string;
        status?: string;
        url: string;
        detail?: string;
        title?: string;
    };
    read_at: string | null;
    created_at: string;
}
interface Inbox {
    data: Notice[];
    unread: number;
    last_page: number;
}

export default function NotificationCenter() {
    const { t, i18n } = useTranslation('notifications');
    const { session } = useSubuserPreview();
    const [open, setOpen] = useState(false);
    const [unread, setUnread] = useState(false);
    const [page, setPage] = useState(1);
    const [busy, setBusy] = useState(false);
    const [failure, setFailure] = useState(false);
    const [failedAction, setFailedAction] = useState<(() => Promise<unknown>) | null>(null);
    const { data, error, mutate } = useSWR<Inbox>(
        session ? null : ['/api/client/notifications', unread, page],
        async ([url, onlyUnread, currentPage]: [string, boolean, number]) =>
            (await http.get(url, { params: { unread: onlyUnread ? 1 : 0, page: currentPage } })).data,
        { refreshInterval: 30000, revalidateOnFocus: true, keepPreviousData: true },
    );
    const act = async (action: () => Promise<unknown>) => {
        setBusy(true);
        setFailure(false);
        setFailedAction(null);
        try {
            await action();
            const refreshed = await mutate();
            if (refreshed && page > refreshed.last_page) setPage(refreshed.last_page);
        } catch {
            setFailure(true);
            setFailedAction(() => action);
        } finally {
            setBusy(false);
        }
    };
    if (session) return null;
    const button =
        'rounded-ui p-2 text-gray-400 hover:bg-gray-800 hover:text-gray-100 focus-visible:outline-2 focus-visible:outline-reviactyl disabled:opacity-50';
    return (
        <>
            <button
                type='button'
                onClick={() => setOpen(true)}
                aria-haspopup='dialog'
                aria-expanded={open}
                className='flex w-full items-center gap-3 rounded-ui px-3 py-2.5 text-sm font-medium text-gray-300 transition-colors hover:bg-gray-800/60 hover:text-gray-100 focus-visible:outline-2 focus-visible:outline-reviactyl'
            >
                <BellIcon className='h-5 w-5 shrink-0' />
                <span>{t('title')}</span>
                {!!data?.unread && (
                    <span
                        className='ml-auto rounded-full bg-gray-800 px-2 py-0.5 text-xs font-semibold text-gray-100'
                        aria-label={t('unread-count', { count: data.unread })}
                    >
                        {data.unread > 99 ? '99+' : data.unread}
                    </span>
                )}
            </button>
            <Dialog open={open} onClose={setOpen} className='relative z-9999'>
                <DialogBackdrop
                    transition
                    className='fixed inset-0 bg-black/20 transition-opacity duration-200 data-closed:opacity-0 motion-reduce:duration-0'
                />
                <div className='pointer-events-none fixed inset-0 flex items-end p-2 sm:bottom-4 sm:left-64 sm:right-auto sm:top-auto sm:p-0 2xl:left-72'>
                    <DialogPanel
                        transition
                        className='pointer-events-auto flex h-[min(36rem,calc(100dvh-2rem))] w-full flex-col overflow-hidden rounded-ui bg-gray-900 text-gray-100 shadow-xl ring-1 ring-gray-700/50 transition duration-200 ease-out data-closed:translate-y-3 data-closed:opacity-0 sm:w-[26rem] sm:data-closed:-translate-x-3 sm:data-closed:translate-y-0 motion-reduce:transform-none motion-reduce:transition-none'
                    >
                        <div className='flex shrink-0 items-start justify-between border-b border-gray-800/70 px-5 py-4'>
                            <div>
                                <DialogTitle className='text-base font-semibold'>{t('title')}</DialogTitle>
                                <p className='mt-1 text-xs leading-relaxed text-gray-400'>{t('description')}</p>
                            </div>
                            <button
                                type='button'
                                className={button}
                                onClick={() => setOpen(false)}
                                aria-label={t('close')}
                            >
                                <XIcon className='h-5 w-5' />
                            </button>
                        </div>
                        <div className='flex shrink-0 items-center justify-between gap-2 border-b border-gray-800/70 px-4 py-2'>
                            <div className='flex gap-1'>
                                {[false, true].map((value) => (
                                    <button
                                        type='button'
                                        key={String(value)}
                                        aria-pressed={unread === value}
                                        onClick={() => {
                                            setUnread(value);
                                            setPage(1);
                                        }}
                                        className={`rounded-ui px-3 py-1.5 text-xs font-medium focus-visible:outline-2 focus-visible:outline-reviactyl ${unread === value ? 'bg-gray-800 text-gray-100' : 'text-gray-400 hover:text-gray-100'}`}
                                    >
                                        {t(value ? 'unread' : 'all')}
                                    </button>
                                ))}
                            </div>
                            <button
                                type='button'
                                disabled={busy || !data?.unread}
                                onClick={() => act(() => http.post('/api/client/notifications/read-all'))}
                                className={`${button} text-xs`}
                            >
                                {t('read-all')}
                            </button>
                        </div>
                        {(failure || error) && (
                            <div role='alert' className='m-4 rounded-ui bg-danger/10 p-3 text-sm text-danger'>
                                {t('error')}{' '}
                                <button
                                    type='button'
                                    onClick={() => {
                                        if (failedAction) void act(failedAction);
                                        else void mutate();
                                    }}
                                    className='underline'
                                >
                                    {t('retry')}
                                </button>
                            </div>
                        )}
                        <div
                            className='min-h-0 flex-1 overflow-y-auto [scrollbar-gutter:stable]'
                            aria-busy={!data || busy}
                        >
                            {!data && !error ? (
                                <p role='status' className='p-6 text-sm text-gray-400'>
                                    {t('loading')}
                                </p>
                            ) : data?.data.length === 0 ? (
                                <div className='px-8 py-20 text-center'>
                                    <BellIcon className='mx-auto mb-4 h-8 w-8 text-gray-500' />
                                    <p className='font-semibold'>{t(unread ? 'caught-up' : 'empty')}</p>
                                    <p className='mt-2 text-sm text-gray-400'>{t('empty-description')}</p>
                                </div>
                            ) : (
                                data?.data.map((notice) => (
                                    <article key={notice.id} className='border-b border-gray-800/70 px-5 py-4'>
                                        <div className='flex items-start gap-3'>
                                            <span
                                                className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-ui ${notice.data.event.includes('fail') || notice.data.status === 'danger' ? 'bg-danger/10 text-danger' : 'bg-gray-800 text-gray-400'}`}
                                            >
                                                {notice.data.event.includes('fail') ||
                                                notice.data.status === 'danger' ||
                                                notice.data.status === 'warning' ? (
                                                    <ExclamationCircleIcon className='h-5 w-5' />
                                                ) : notice.data.status === 'success' ? (
                                                    <CheckCircleIcon className='h-5 w-5' />
                                                ) : notice.data.event === 'custom' ? (
                                                    <BellIcon className='h-5 w-5' />
                                                ) : notice.data.event.startsWith('backup') ? (
                                                    <ArchiveIcon className='h-5 w-5' />
                                                ) : (
                                                    <ServerIcon className='h-5 w-5' />
                                                )}
                                            </span>
                                            <div className='min-w-0 flex-1'>
                                                <div className='flex items-center gap-2'>
                                                    <h3
                                                        className={`text-sm ${notice.read_at ? 'font-medium text-gray-300' : 'font-semibold text-gray-100'}`}
                                                    >
                                                        {notice.data.title || t(`events.${notice.data.event}`)}
                                                    </h3>
                                                    {!notice.read_at && (
                                                        <span
                                                            className='ml-auto h-2 w-2 shrink-0 rounded-full bg-reviactyl'
                                                            role='img'
                                                            aria-label={t('unread')}
                                                        />
                                                    )}
                                                </div>
                                                <p className='mt-0.5 break-words text-xs leading-relaxed text-gray-400'>
                                                    {notice.data.body ?? notice.data.server}
                                                </p>
                                                {notice.data.detail && (
                                                    <p className='mt-1 break-words text-xs text-gray-400'>
                                                        {notice.data.detail}
                                                    </p>
                                                )}
                                                <time
                                                    dateTime={notice.created_at}
                                                    className='mt-2 block text-xs text-gray-400'
                                                >
                                                    {new Date(notice.created_at).toLocaleString(i18n.language, {
                                                        month: 'short',
                                                        day: 'numeric',
                                                        hour: 'numeric',
                                                        minute: '2-digit',
                                                    })}
                                                </time>
                                            </div>
                                        </div>
                                        <div className='mt-2 flex items-center justify-between gap-2 pl-12'>
                                            <Link
                                                to={
                                                    /^\/(?!\/)[^\\\s]*$/.test(notice.data.url || '/')
                                                        ? notice.data.url || '/'
                                                        : '/'
                                                }
                                                reloadDocument={(notice.data.url || '/').startsWith('/admin/')}
                                                onClick={() => {
                                                    if (!notice.read_at)
                                                        void act(() =>
                                                            http.patch(`/api/client/notifications/${notice.id}`, {
                                                                read: true,
                                                            }),
                                                        );
                                                    setOpen(false);
                                                }}
                                                className='flex items-center gap-1.5 text-xs font-medium text-gray-300 transition-colors hover:text-gray-100'
                                            >
                                                {t(
                                                    notice.data.event === 'custom' ||
                                                        notice.data.event === 'removed' ||
                                                        (notice.data.url || '/').startsWith('/admin/')
                                                        ? 'view-panel'
                                                        : 'view-server',
                                                )}
                                                <ArrowRightIcon className='h-4 w-4' />
                                            </Link>
                                            <div className='flex'>
                                                <button
                                                    type='button'
                                                    disabled={busy}
                                                    className={button}
                                                    title={t(notice.read_at ? 'mark-unread' : 'mark-read')}
                                                    aria-label={t(notice.read_at ? 'mark-unread' : 'mark-read')}
                                                    onClick={() =>
                                                        act(() =>
                                                            http.patch(`/api/client/notifications/${notice.id}`, {
                                                                read: !notice.read_at,
                                                            }),
                                                        )
                                                    }
                                                >
                                                    <CheckIcon className='h-4 w-4' />
                                                </button>
                                                <button
                                                    type='button'
                                                    disabled={busy}
                                                    className={button}
                                                    aria-label={t('dismiss')}
                                                    title={t('dismiss')}
                                                    onClick={() =>
                                                        act(() => http.delete(`/api/client/notifications/${notice.id}`))
                                                    }
                                                >
                                                    <TrashIcon className='h-4 w-4' />
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                ))
                            )}
                        </div>
                        {data && data.last_page > 1 && (
                            <div className='flex shrink-0 items-center justify-between border-t border-gray-800/70 px-4 py-2'>
                                <button
                                    type='button'
                                    className={button}
                                    disabled={page === 1 || busy}
                                    onClick={() => setPage(page - 1)}
                                >
                                    {t('previous')}
                                </button>
                                <span className='text-sm text-gray-400'>
                                    {page} / {data.last_page}
                                </span>
                                <button
                                    type='button'
                                    className={button}
                                    disabled={page >= data.last_page || busy}
                                    onClick={() => setPage(page + 1)}
                                >
                                    {t('next')}
                                </button>
                            </div>
                        )}
                    </DialogPanel>
                </div>
            </Dialog>
        </>
    );
}
