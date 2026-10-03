import { useEffect, useRef, useState, type MouseEvent } from 'react';
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import { FaBoxArchive, FaEllipsis, FaLock } from 'react-icons/fa6';
import { format, formatDistanceToNow } from 'date-fns';
import Spinner from '@/reviactyl/elements/Spinner';
import { bytesToString } from '@/lib/formatters';
import Can from '@/reviactyl/elements/Can';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';
import BackupContextMenu, { BackupContextMenuHandle } from '@/components/server/backups/BackupContextMenu';
import BackupCompletedCheck from '@/components/server/backups/BackupCompletedCheck';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import getServerBackups from '@/api/swr/getServerBackups';
import { ServerBackup } from '@/api/server/types';
import { SocketEvent } from '@/components/server/events';
import { useTranslation } from 'react-i18next';

interface Props {
    backup: ServerBackup;
    className?: string;
}

export default ({ backup, className }: Props) => {
    const { t } = useTranslation('server/backups');
    const { mutate } = getServerBackups();
    const reduceMotion = useReducedMotion();
    const contextMenuRef = useRef<BackupContextMenuHandle>(null);
    const [justCompleted, setJustCompleted] = useState(false);

    useEffect(() => {
        if (!justCompleted) return;

        const timeout = setTimeout(() => setJustCompleted(false), 1600);

        return () => clearTimeout(timeout);
    }, [justCompleted]);

    const handleContextMenu = (e: MouseEvent) => {
        if (!backup.completedAt) return;
        e.preventDefault();
        e.stopPropagation();
        contextMenuRef.current?.triggerMenu(e.clientX);
    };

    useWebsocketEvent(SocketEvent.BACKUP_COMPLETED, (data) => {
        try {
            const parsed = JSON.parse(data);
            if (parsed.uuid !== backup.uuid) return;

            const isSuccessful = parsed.is_successful ?? true;
            if (backup.completedAt === null && isSuccessful) {
                setJustCompleted(true);
            }

            mutate(
                (data) =>
                    data && {
                        ...data,
                        items: data.items.map((b) =>
                            b.uuid !== backup.uuid
                                ? b
                                : {
                                      ...b,
                                      // Older Wings versions omit this field from successful completion events.
                                      isSuccessful,
                                      checksum: (parsed.checksum_type || '') + ':' + (parsed.checksum || ''),
                                      bytes: parsed.file_size || 0,
                                      completedAt: new Date(),
                                  },
                        ),
                    },
                false,
            );
        } catch (e) {
            console.warn(e);
        }
    });

    return (
        <GreyRowBox
            className={`flex-wrap items-center md:flex-nowrap ${className || ''}`}
            onContextMenu={handleContextMenu}
        >
            <div className='flex w-full items-center truncate md:flex-1'>
                <div className='mr-4 flex h-4 w-4 shrink-0 items-center justify-center'>
                    <AnimatePresence mode='wait' initial={false}>
                        <motion.div
                            className='flex'
                            key={backup.completedAt === null ? 'pending' : justCompleted ? 'check' : 'done'}
                            initial={reduceMotion ? false : { opacity: 0, scale: 0.8 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={reduceMotion ? { opacity: 1, scale: 1 } : { opacity: 0, scale: 0.8 }}
                            transition={{ duration: reduceMotion ? 0 : 0.15 }}
                        >
                            {backup.completedAt === null ? (
                                <Spinner size={'small'} />
                            ) : justCompleted ? (
                                <BackupCompletedCheck />
                            ) : backup.isLocked ? (
                                <FaLock className={'text-yellow-500'} />
                            ) : (
                                <FaBoxArchive className={'text-gray-300'} />
                            )}
                        </motion.div>
                    </AnimatePresence>
                </div>
                <div className='flex flex-col truncate'>
                    <div className='mb-1 flex items-center text-sm'>
                        {backup.completedAt !== null && !backup.isSuccessful && (
                            <span className='mr-2 rounded-full border border-red-600 bg-red-500 px-2 py-px text-xs uppercase text-white'>
                                {t('failed')}
                            </span>
                        )}
                        <p className='truncate break-words'>{backup.name}</p>
                        <span className='ml-2 rounded border border-gray-700 px-1 text-xs text-gray-300'>
                            {backup.format}
                        </span>
                        {backup.completedAt !== null && backup.isSuccessful && (
                            <span className='ml-3 hidden text-xs font-extralight text-gray-300 sm:inline'>
                                {bytesToString(backup.bytes)}
                            </span>
                        )}
                    </div>
                    <p className='mt-1 truncate font-mono text-xs text-gray-400 md:mt-0'>{backup.checksum}</p>
                </div>
            </div>
            <div className='mt-4 flex-1 md:mt-0 md:ml-8 md:w-48 md:flex-none md:text-center'>
                <p title={format(backup.createdAt, 'ddd, MMMM do, yyyy HH:mm:ss')} className='text-sm'>
                    {formatDistanceToNow(backup.createdAt, { includeSeconds: true, addSuffix: true })}
                </p>
                <p className='mt-1 text-2xs uppercase text-muted'>{t('created')}</p>
            </div>
            <Can
                action={
                    backup.isSuccessful
                        ? ['backup.create', 'backup.download', 'backup.restore', 'backup.delete']
                        : 'backup.delete'
                }
                matchAny={backup.isSuccessful}
            >
                <div className='mt-4 ml-6 md:mt-0' style={{ marginRight: '-0.5rem' }}>
                    {!backup.completedAt ? (
                        <div className='invisible p-2'>
                            <FaEllipsis />
                        </div>
                    ) : (
                        <BackupContextMenu ref={contextMenuRef} backup={backup} />
                    )}
                </div>
            </Can>
        </GreyRowBox>
    );
};
