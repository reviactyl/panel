import { useEffect, useState } from 'react';
import tw from 'twin.macro';
import { Button } from '@/reviactyl/elements/button/index';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import useFlash from '@/plugins/useFlash';
import compressFiles from '@/api/server/files/compressFiles';
import { ServerContext } from '@/state/server';
import deleteFiles from '@/api/server/files/deleteFiles';
import RenameFileModal from '@/components/server/files/RenameFileModal';
import { Dialog } from '@/reviactyl/elements/dialog';
import { useTranslation } from 'react-i18next';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { FaFileArrowUp, FaTrash } from 'react-icons/fa6';
import Spinner from '@/reviactyl/elements/Spinner';
import { FaFileArchive } from 'react-icons/fa';

const MassActionsBar = () => {
    const { t } = useTranslation('server/files');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);

    const { mutate } = useFileManagerSwr();
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [loading, setLoading] = useState(false);
    const [loadingMessage, setLoadingMessage] = useState('');
    const [showConfirm, setShowConfirm] = useState(false);
    const [showMove, setShowMove] = useState(false);
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    const selectedFiles = ServerContext.useStoreState((state) => state.files.selectedFiles);
    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);

    useEffect(() => {
        if (!loading) setLoadingMessage('');
    }, [loading]);

    const onClickCompress = () => {
        setLoading(true);
        clearFlashes('files');
        setLoadingMessage(t('mass-actions.archiving'));

        compressFiles(uuid, directory, selectedFiles)
            .then(() => mutate())
            .then(() => setSelectedFiles([]))
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setLoading(false));
    };

    const onClickConfirmDeletion = () => {
        setLoading(true);
        setShowConfirm(false);
        clearFlashes('files');
        setLoadingMessage(t('mass-actions.deleting'));

        deleteFiles(uuid, directory, selectedFiles)
            .then(() => {
                mutate((files) => files.filter((f) => selectedFiles.indexOf(f.name) < 0), false);
                setSelectedFiles([]);
            })
            .catch((error) => {
                mutate();
                clearAndAddHttpError({ key: 'files', error });
            })
            .then(() => setLoading(false));
    };

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
                <RenameFileModal
                    files={selectedFiles}
                    visible
                    appear
                    useMoveTerminology
                    onDismissed={() => setShowMove(false)}
                />
            )}
            <span className='border-l border-gray-600 h-5 mx-2' />
            <Tooltip content={t('move')}>
                <Button.Text onClick={() => setShowMove(true)} aria-label={t('move')} disabled={loading}>
                    <FaFileArrowUp className='h-5 w-5' />
                </Button.Text>
            </Tooltip>
            <Tooltip content={t('archive')}>
                <Button.Success onClick={onClickCompress} aria-label={t('archive')} disabled={loading}>
                    <FaFileArchive className='h-5 w-5' />
                </Button.Success>
            </Tooltip>
            <Tooltip content={t('delete')}>
                <Button.Danger onClick={() => setShowConfirm(true)} aria-label={t('delete')} disabled={loading}>
                    <FaTrash className='h-5 w-5' />
                </Button.Danger>
            </Tooltip>
            {loading && (
                <Tooltip content={loadingMessage}>
                    <Button onClick={onClickCompress} aria-label={loadingMessage} className='cursor-wait'>
                        <Spinner css={tw`h-5 w-5`} />
                    </Button>
                </Tooltip>
            )}
        </>
    );
};

export default MassActionsBar;
