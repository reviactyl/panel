import { useState, useRef, forwardRef, useImperativeHandle } from 'react';
import { FaBoxOpen, FaCloudArrowDown, FaEllipsis, FaLock, FaTrash, FaUnlock } from 'react-icons/fa6';
import DropdownMenu, { DropdownButtonRow } from '@/components/elements/DropdownMenu';
import getBackupDownloadUrl from '@/api/server/backups/getBackupDownloadUrl';
import useFlash from '@/plugins/useFlash';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import deleteBackup from '@/api/server/backups/deleteBackup';
import Can from '@/components/elements/Can';
import tw from 'twin.macro';
import getServerBackups from '@/api/swr/getServerBackups';
import { ServerBackup } from '@/api/server/types';
import { ServerContext } from '@/state/server';
import Input from '@/components/elements/Input';
import { restoreServerBackup } from '@/api/server/backups';
import http, { httpErrorToHuman } from '@/api/http';
import { Dialog } from '@/components/elements/dialog';
import { useTranslation } from 'react-i18next';

interface Props {
    backup: ServerBackup;
}

export interface BackupContextMenuHandle {
    triggerMenu: (posX: number) => void;
}

const BackupContextMenu = forwardRef<BackupContextMenuHandle, Props>(({ backup }, ref) => {
    const { t } = useTranslation('server/backups');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);
    const [modal, setModal] = useState('');
    const [loading, setLoading] = useState(false);
    const [truncate, setTruncate] = useState(false);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { mutate } = getServerBackups();
    const dropdownRef = useRef<DropdownMenu>(null);

    useImperativeHandle(ref, () => ({
        triggerMenu: (posX: number) => {
            dropdownRef.current?.triggerMenu(posX);
        },
    }));

    const doDownload = () => {
        setLoading(true);
        clearFlashes('backups');
        getBackupDownloadUrl(uuid, backup.uuid)
            .then((url) => {
                window.location.href = url;
            })
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ key: 'backups', error });
            })
            .then(() => setLoading(false));
    };

    const doDeletion = () => {
        setLoading(true);
        clearFlashes('backups');
        deleteBackup(uuid, backup.uuid)
            .then(() =>
                mutate(
                    (data) => ({
                        ...data,
                        items: data.items.filter((b) => b.uuid !== backup.uuid),
                        backupCount: data.backupCount - 1,
                    }),
                    false
                )
            )
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ key: 'backups', error });
                setLoading(false);
                setModal('');
            });
    };

    const doRestorationAction = () => {
        setLoading(true);
        clearFlashes('backups');
        restoreServerBackup(uuid, backup.uuid, truncate)
            .then(() =>
                setServerFromState((s) => ({
                    ...s,
                    status: 'restoring_backup',
                }))
            )
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ key: 'backups', error });
            })
            .then(() => setLoading(false))
            .then(() => setModal(''));
    };

    const onLockToggle = () => {
        if (backup.isLocked && modal !== 'unlock') {
            return setModal('unlock');
        }

        http.post(`/api/client/servers/${uuid}/backups/${backup.uuid}/lock`)
            .then(() =>
                mutate(
                    (data) => ({
                        ...data,
                        items: data.items.map((b) =>
                            b.uuid !== backup.uuid
                                ? b
                                : {
                                      ...b,
                                      isLocked: !b.isLocked,
                                  }
                        ),
                    }),
                    false
                )
            )
            .catch((error) => alert(httpErrorToHuman(error)))
            .then(() => setModal(''));
    };

    return (
        <>
            <Dialog.Confirm
                open={modal === 'unlock'}
                onClose={() => setModal('')}
                title={t('unlock-backup', { name: backup.name })}
                onConfirmed={onLockToggle}
            >
                {t('unlock-message')}
            </Dialog.Confirm>
            <Dialog.Confirm
                open={modal === 'restore'}
                onClose={() => setModal('')}
                confirm={'Restore'}
                title={t('restore-backup', { name: backup.name })}
                onConfirmed={() => doRestorationAction()}
            >
                <p>{t('restore-message')}</p>
                <p css={tw`mt-4 -mb-2 bg-gray-700 p-3 rounded`}>
                    <label htmlFor={'restore_truncate'} css={tw`text-base flex items-center cursor-pointer`}>
                        <Input
                            type={'checkbox'}
                            css={tw`text-red-500! w-5! h-5! mr-2`}
                            id={'restore_truncate'}
                            value={'true'}
                            checked={truncate}
                            onChange={() => setTruncate((s) => !s)}
                        />
                        {t('delete-all')}
                    </label>
                </p>
            </Dialog.Confirm>
            <Dialog.Confirm
                title={t('delete-backup', { name: backup.name })}
                confirm={'Continue'}
                open={modal === 'delete'}
                onClose={() => setModal('')}
                onConfirmed={doDeletion}
            >
                {t('delete-message')}
            </Dialog.Confirm>
            <SpinnerOverlay visible={loading} fixed />
            {backup.isSuccessful ? (
                <DropdownMenu
                    ref={dropdownRef}
                    renderToggle={(onClick) => (
                        <button
                            onClick={onClick}
                            css={tw`text-gray-200 transition-colors duration-150 hover:text-gray-100 p-2`}
                        >
                            <FaEllipsis />
                        </button>
                    )}
                >
                    <div css={tw`text-sm`}>
                        <Can action={'backup.download'}>
                            <DropdownButtonRow onClick={doDownload}>
                                <FaCloudArrowDown className={'text-xs inline-block w-[1.25em]'} />
                                <span css={tw`ml-2`}>{t('download')}</span>
                            </DropdownButtonRow>
                        </Can>
                        <Can action={'backup.restore'}>
                            <DropdownButtonRow onClick={() => setModal('restore')}>
                                <FaBoxOpen className={'text-xs inline-block w-[1.25em]'} />
                                <span css={tw`ml-2`}>{t('restore')}</span>
                            </DropdownButtonRow>
                        </Can>
                        <Can action={'backup.delete'}>
                            <>
                                <DropdownButtonRow onClick={onLockToggle}>
                                    {backup.isLocked ? (
                                        <FaUnlock className={'text-xs mr-2 inline-block w-[1.25em]'} />
                                    ) : (
                                        <FaLock className={'text-xs mr-2 inline-block w-[1.25em]'} />
                                    )}
                                    {backup.isLocked ? t('unlock') : t('lock')}
                                </DropdownButtonRow>
                                {!backup.isLocked && (
                                    <DropdownButtonRow danger onClick={() => setModal('delete')}>
                                        <FaTrash className={'text-xs inline-block w-[1.25em]'} />
                                        <span css={tw`ml-2`}>{t('delete')}</span>
                                    </DropdownButtonRow>
                                )}
                            </>
                        </Can>
                    </div>
                </DropdownMenu>
            ) : (
                <button
                    onClick={() => setModal('delete')}
                    css={tw`text-gray-200 transition-colors duration-150 hover:text-gray-100 p-2`}
                >
                    <FaTrash />
                </button>
            )}
        </>
    );
});

export default BackupContextMenu;
