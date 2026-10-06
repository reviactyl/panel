import { useEffect, useRef, useState } from 'react';
import { Button } from '@/reviactyl/components/button/index';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import useFlash from '@/plugins/useFlash';
import compressFiles from '@/api/server/files/compressFiles';
import { ServerContext } from '@/state/server';
import deleteFiles from '@/api/server/files/deleteFiles';
import MoveFileModal from '@/components/server/files/MoveFileModal';
import { MASS_ACTION_EVENT, MassAction } from '@/components/server/files/FileDropdownMenu';
import useEventListener from '@/plugins/useEventListener';
import { Dialog } from '@/reviactyl/elements/dialog';
import { useTranslation } from 'react-i18next';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { FaFileArrowUp, FaTrash } from 'react-icons/fa6';
import Spinner from '@/reviactyl/elements/Spinner';
import { FaFileArchive } from 'react-icons/fa';
import Can from '@/reviactyl/elements/Can';
import { useStoreState } from 'easy-peasy';

const MassActionsBar = () => {
    const { t } = useTranslation('server/files');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);

    const { data: currentDirectoryFiles, mutate } = useFileManagerSwr();
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [loading, setLoading] = useState(false);
    const [loadingMessage, setLoadingMessage] = useState('');
    const [showConfirm, setShowConfirm] = useState(false);
    const [showMove, setShowMove] = useState(false);
    const [showFormatMenu, setShowFormatMenu] = useState(false);
    const archiveRef = useRef<HTMLDivElement>(null);
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    const selectedFiles = ServerContext.useStoreState((state) => state.files.selectedFiles);
    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);
    const setMassActionFiles = ServerContext.useStoreActions((actions) => actions.files.setMassActionFiles);

    const selectedDirectoryNames = (currentDirectoryFiles ?? [])
        .filter((file) => !file.isFile && selectedFiles.includes(file.name))
        .map((file) => file.name);

    useEffect(() => {
        if (!loading) setLoadingMessage('');
    }, [loading]);

    // Tracks the files being worked on so their context menus can't start a conflicting action, even
    // if the selection changes before the request finishes.
    const setRunning = (running: boolean) => {
        setLoading(running);
        setMassActionFiles(running ? selectedFiles : []);
    };

    useEffect(() => {
        if (!showFormatMenu) return;
        const closeOnOutsideTap = (event: PointerEvent) => {
            if (!archiveRef.current?.contains(event.target as Node)) setShowFormatMenu(false);
        };
        document.addEventListener('pointerdown', closeOnOutsideTap);
        return () => document.removeEventListener('pointerdown', closeOnOutsideTap);
    }, [showFormatMenu]);

    const archiveFormat = useStoreState((state) => state.user.data!.archiveFormat);

    const onClickCompress = (format: 'tar.gz' | 'zip') => {
        setShowFormatMenu(false);
        setRunning(true);
        clearFlashes('files');
        setLoadingMessage(t('mass-actions.archiving'));

        compressFiles(uuid, directory, selectedFiles, format)
            .then(() => mutate())
            .then(() => setSelectedFiles([]))
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setRunning(false));
    };

    const onClickConfirmDeletion = () => {
        setRunning(true);
        setShowConfirm(false);
        clearFlashes('files');
        setLoadingMessage(t('mass-actions.deleting'));

        deleteFiles(uuid, directory, selectedFiles)
            .then(() => {
                mutate((files) => files?.filter((f) => selectedFiles.indexOf(f.name) < 0), false);
                setSelectedFiles([]);
            })
            .catch((error) => {
                mutate();
                clearAndAddHttpError({ key: 'files', error });
            })
            .then(() => setRunning(false));
    };

    useEventListener(MASS_ACTION_EVENT, ({ detail }: CustomEvent<MassAction>) => {
        if (loading) return;

        if (detail.type === 'move') setShowMove(true);
        else if (detail.type === 'delete') setShowConfirm(true);
        else if (detail.type === 'archive') onClickCompress(detail.format);
    });

    return (
        <>
            <Dialog.Confirm
                title={t('mass-actions.delete-title')}
                open={showConfirm}
                confirm={t('mass-actions.delete-confirm')}
                onClose={() => setShowConfirm(false)}
                onConfirmed={onClickConfirmDeletion}
            >
                <p className={'mb-2'}>
                    {t('mass-actions.delete-message-start')}&nbsp;
                    <span className={'font-semibold text-gray-50'}>
                        {selectedFiles.length} {t('mass-actions.delete-message-files')}
                    </span>
                    {t('mass-actions.delete-message-end')}
                    {selectedFiles.slice(0, 15).map((file) => (
                        <li key={file}>{file}</li>
                    ))}
                    {selectedFiles.length > 15 && <li>and {selectedFiles.length - 15} others</li>}
                </p>
            </Dialog.Confirm>
            {showMove && (
                <MoveFileModal
                    files={selectedFiles}
                    directoryNames={selectedDirectoryNames}
                    visible
                    appear
                    onDismissed={() => setShowMove(false)}
                />
            )}
            <Can action={['file.update', 'file.archive', 'file.delete']} matchAny>
                <span className='border-l border-gray-600 h-5 mx-2' />
            </Can>
            <Can action={'file.update'}>
                <Tooltip content={t('move')}>
                    <Button.Text onClick={() => setShowMove(true)} aria-label={t('move')} disabled={loading}>
                        <FaFileArrowUp className='h-5 w-5' />
                    </Button.Text>
                </Tooltip>
            </Can>
            <Can action={'file.archive'}>
                <div ref={archiveRef} className='group relative flex items-center'>
                    <Button.Success
                        onClick={() => {
                            if (window.matchMedia('(hover: none), (max-width: 639px)').matches) {
                                setShowFormatMenu((open) => !open);
                                return;
                            }
                            onClickCompress(archiveFormat);
                        }}
                        aria-label={t('archive')}
                        disabled={loading}
                    >
                        <FaFileArchive className='h-5 w-5' />
                    </Button.Success>
                    <div
                        style={{ display: showFormatMenu ? 'flex' : undefined }}
                        className='absolute left-[calc(100%-0.5rem)] top-0 z-20 hidden w-48 flex-col rounded-ui border border-gray-800 bg-gray-800 p-2 text-gray-100 shadow-lg group-hover:flex group-focus-within:flex max-sm:left-0 max-sm:top-full'
                    >
                        {(['tar.gz', 'zip'] as const).map((format) => (
                            <button
                                key={format}
                                type='button'
                                onClick={() => onClickCompress(format)}
                                disabled={loading}
                                aria-label={t('archive-as', { format })}
                                className='w-full rounded-ui p-2 text-left text-sm hover:bg-gray-100 hover:text-gray-800 focus:bg-gray-100 focus:text-gray-800'
                            >
                                {format}
                            </button>
                        ))}
                    </div>
                </div>
            </Can>
            <Can action={'file.delete'}>
                <Tooltip content={t('delete')}>
                    <Button.Danger onClick={() => setShowConfirm(true)} aria-label={t('delete')} disabled={loading}>
                        <FaTrash className='h-5 w-5' />
                    </Button.Danger>
                </Tooltip>
            </Can>
            {loading && (
                <Tooltip content={loadingMessage}>
                    <Button disabled aria-label={loadingMessage} className='cursor-wait'>
                        <Spinner className='h-5 w-5' />
                    </Button>
                </Tooltip>
            )}
        </>
    );
};

export default MassActionsBar;
