import { useSWRConfig } from 'swr';
import { useEffect, useId, useRef, useState } from 'react';
import { Button } from '@/reviactyl/components/button/index';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import useFlash from '@/plugins/useFlash';
import compressFiles from '@/api/server/files/compressFiles';
import { ServerContext } from '@/state/server';
import deleteFiles from '@/api/server/files/deleteFiles';
import MoveFileModal from '@/components/server/files/MoveFileModal';
import { MASS_ACTION_EVENT, MassAction } from '@/components/server/files/FileDropdownMenu';
import useEventListener from '@/plugins/useEventListener';
import DeleteFilesDialog from './DeleteFilesDialog';
import { useTranslation } from 'react-i18next';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { FaFileArrowUp, FaTrash } from 'react-icons/fa6';
import Spinner from '@/reviactyl/elements/Spinner';
import { FaFileArchive } from 'react-icons/fa';
import Can from '@/reviactyl/elements/Can';
import { useStoreState } from 'easy-peasy';
import { join } from 'pathe';
import useArchiveMenuPosition from './useArchiveMenuPosition';

const MassActionsBar = () => {
    const { t } = useTranslation('server/files');
    const { mutate: refreshCache } = useSWRConfig();
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);

    const { data: currentDirectoryFiles, mutate } = useFileManagerSwr();
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [loading, setLoading] = useState(false);
    const [loadingMessage, setLoadingMessage] = useState('');
    const [showConfirm, setShowConfirm] = useState(false);
    const [showMove, setShowMove] = useState(false);
    const [showFormatMenu, setShowFormatMenu] = useState(false);
    const archiveRef = useRef<HTMLDivElement>(null);
    const formatMenuRef = useRef<HTMLDivElement>(null);
    const archiveHovered = useRef(false);
    const formatMenuId = useId();
    const formatMenuPosition = useArchiveMenuPosition(showFormatMenu, archiveRef, formatMenuRef);
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

    // Tracks the full paths of the files being worked on so their context menus can't start a conflicting
    // action, even if the selection changes before the request finishes.
    const setRunning = (running: boolean) => {
        setLoading(running);
        setMassActionFiles(running ? selectedFiles.map((name) => join(directory, name)) : []);
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

    const onClickConfirmDeletion = (permanent: boolean) => {
        setRunning(true);
        setShowConfirm(false);
        clearFlashes('files');
        setLoadingMessage(t('mass-actions.deleting'));

        deleteFiles(uuid, directory, selectedFiles, permanent)
            .then(() => {
                mutate((files) => files?.filter((f) => selectedFiles.indexOf(f.name) < 0), false);
                setSelectedFiles([]);
                return refreshCache(`/api/client/servers/${uuid}/files/recycle-bin`);
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
            {showConfirm && (
                <DeleteFilesDialog
                    files={selectedFiles}
                    onClose={() => setShowConfirm(false)}
                    onConfirm={onClickConfirmDeletion}
                />
            )}
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
                <div
                    ref={archiveRef}
                    className='relative flex items-center'
                    onMouseEnter={() => {
                        archiveHovered.current = true;
                        if (!loading) setShowFormatMenu(true);
                    }}
                    onMouseLeave={(event) => {
                        archiveHovered.current = false;
                        if (!event.currentTarget.contains(document.activeElement)) setShowFormatMenu(false);
                    }}
                    onFocusCapture={() => !loading && setShowFormatMenu(true)}
                    onBlur={(event) => {
                        if (!archiveHovered.current && !event.currentTarget.contains(event.relatedTarget)) {
                            setShowFormatMenu(false);
                        }
                    }}
                    onKeyDown={(event) => {
                        if (event.key === 'Escape') {
                            event.preventDefault();
                            archiveRef.current?.querySelector('button')?.focus();
                            setShowFormatMenu(false);
                        }
                    }}
                >
                    <Button.Success
                        onClick={() => {
                            if (window.matchMedia('(hover: none), (max-width: 639px)').matches) {
                                setShowFormatMenu(true);
                                return;
                            }
                            onClickCompress(archiveFormat);
                        }}
                        aria-label={t('archive')}
                        aria-expanded={showFormatMenu}
                        aria-controls={formatMenuId}
                        disabled={loading}
                    >
                        <FaFileArchive className='h-5 w-5' />
                    </Button.Success>
                    <div
                        ref={formatMenuRef}
                        id={formatMenuId}
                        role='group'
                        aria-label={t('archive')}
                        style={{ ...formatMenuPosition, display: showFormatMenu ? 'flex' : 'none' }}
                        className='fixed z-50 w-48 flex-col rounded-ui border border-gray-800 bg-gray-800 p-2 text-gray-100 shadow-lg'
                    >
                        {(['tar.gz', 'zip'] as const).map((format) => (
                            <button
                                key={format}
                                type='button'
                                onClick={() => onClickCompress(format)}
                                disabled={loading}
                                aria-label={t('archive-as', { format })}
                                className='w-full shrink-0 rounded-ui p-2 text-left text-sm transition-colors duration-150 enabled:hover:bg-gray-700 focus-visible:bg-gray-700 focus-visible:outline-solid focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-current'
                            >
                                {format}
                            </button>
                        ))}
                    </div>
                </div>
            </Can>
            <Can action={'file.delete'}>
                <Tooltip content={t('delete')}>
                    <Button onClick={() => setShowConfirm(true)} aria-label={t('delete')} disabled={loading}>
                        <FaTrash className='h-5 w-5' />
                    </Button>
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
