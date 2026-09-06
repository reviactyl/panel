import { useRef, type MouseEvent } from 'react';
import { FaBoxArchive, FaEllipsis, FaLock } from 'react-icons/fa6';
import { format, formatDistanceToNow } from 'date-fns';
import Spinner from '@/reviactyl/elements/Spinner';
import { bytesToString } from '@/lib/formatters';
import Can from '@/reviactyl/elements/Can';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';
import BackupContextMenu, { BackupContextMenuHandle } from '@/components/server/backups/BackupContextMenu';
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
    const contextMenuRef = useRef<BackupContextMenuHandle>(null);

    const handleContextMenu = (e: MouseEvent) => {
        if (!backup.completedAt) return;
        e.preventDefault();
        e.stopPropagation();
        contextMenuRef.current?.triggerMenu(e.clientX);
    };

    useWebsocketEvent(`${SocketEvent.BACKUP_COMPLETED}:${backup.uuid}` as SocketEvent, (data) => {
        try {
            const parsed = JSON.parse(data);

            mutate(
                (data) => ({
                    ...data,
                    items: data.items.map((b) =>
                        b.uuid !== backup.uuid
                            ? b
                            : {
                                  ...b,
                                  // Older Wings versions omit this field from successful completion events.
                                  isSuccessful: parsed.is_successful ?? true,
                                  checksum: (parsed.checksum_type || '') + ':' + (parsed.checksum || ''),
                                  bytes: parsed.file_size || 0,
                                  completedAt: new Date(),
                              }
                    ),
                }),
                false
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
                <div className='mr-4'>
                    {backup.completedAt !== null ? (
                        backup.isLocked ? (
                            <FaLock className={'text-yellow-500'} />
                        ) : (
                            <FaBoxArchive className={'text-gray-300'} />
                        )
                    ) : (
                        <Spinner size={'small'} />
                    )}
                </div>
                <div className='flex flex-col truncate'>
                    <div className='mb-1 flex items-center text-sm'>
                        {backup.completedAt !== null && !backup.isSuccessful && (
                            <span className='mr-2 rounded-full border border-red-600 bg-red-500 px-2 py-px text-xs uppercase text-white'>
                                {t('failed')}
                            </span>
                        )}
                        <p className='truncate break-words'>{backup.name}</p>
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
                action={backup.isSuccessful ? ['backup.download', 'backup.restore', 'backup.delete'] : 'backup.delete'}
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
