import React, { memo, useId, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import {
    FaBoxOpen,
    FaCopy,
    FaEllipsis,
    FaFileZipper,
    FaFileCode,
    FaFileArrowDown,
    FaTurnUp,
    FaPen,
    FaTrash,
    FaChevronRight,
} from 'react-icons/fa6';
import { IconType } from 'react-icons';
import RenameFileModal from '@/components/server/files/RenameFileModal';
import MoveFileModal from '@/components/server/files/MoveFileModal';
import { ServerContext } from '@/state/server';
import { join } from 'pathe';
import deleteFiles from '@/api/server/files/deleteFiles';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import copyFile from '@/api/server/files/copyFile';
import Can from '@/reviactyl/elements/Can';
import getFileDownloadUrl from '@/api/server/files/getFileDownloadUrl';
import useFlash from '@/plugins/useFlash';
import { FileObject } from '@/api/server/files/loadDirectory';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import DropdownMenu from '@/reviactyl/elements/DropdownMenu';
import useEventListener from '@/plugins/useEventListener';
import compressFiles from '@/api/server/files/compressFiles';
import decompressFiles from '@/api/server/files/decompressFiles';
import isEqual from 'react-fast-compare';
import ChmodFileModal from '@/components/server/files/ChmodFileModal';
import { Dialog } from '@/reviactyl/elements/dialog';
import { ExtensionSlot } from '@/extensions/ExtensionSlot';
import { useStoreState } from 'easy-peasy';

type ModalType = 'rename' | 'move' | 'chmod';

export const MASS_ACTION_EVENT = 'panel:files:mass-action';

export type MassAction = { type: 'move' } | { type: 'delete' } | { type: 'archive'; format: 'tar.gz' | 'zip' };

interface RowProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    icon: IconType;
    title: string;
    danger?: boolean;
    disabled?: boolean;
}

const Row = ({ icon, title, danger, disabled, className, onClick, ...props }: RowProps) => {
    const ItemIcon = icon;

    return (
        <button
            type='button'
            className={`flex w-full items-center rounded-ui p-2 text-left transition-all duration-150 ${
                disabled
                    ? 'cursor-not-allowed opacity-50'
                    : `cursor-pointer ${
                          danger
                              ? 'hover:bg-red-100 hover:text-red-700 focus-visible:bg-red-100 focus-visible:text-red-700'
                              : 'hover:bg-gray-100 hover:text-gray-800 focus-visible:bg-gray-100 focus-visible:text-gray-800'
                      }`
            } ${className || ''}`}
            onClick={disabled ? undefined : onClick}
            disabled={disabled}
            {...props}
        >
            <ItemIcon className={'text-xs inline-block w-[1.25em]'} />
            <span className='ml-2'>{title}</span>
        </button>
    );
};

const FileDropdownMenu = ({ file }: { file: FileObject }) => {
    const { t } = useTranslation('server/files');
    const onClickRef = useRef<DropdownMenu>(null);
    const menuId = useId();
    const [showSpinner, setShowSpinner] = useState(false);
    const [showFormatMenu, setShowFormatMenu] = useState(false);
    const archiveHovered = useRef(false);
    const archiveItemRef = useRef<HTMLDivElement>(null);
    const formatMenuRef = useRef<HTMLDivElement>(null);
    const [formatMenuPosition, setFormatMenuPosition] = useState<React.CSSProperties>();
    const [modal, setModal] = useState<ModalType | null>(null);
    const [showConfirmation, setShowConfirmation] = useState(false);

    const dismissModal = () => {
        setModal(null);
        onClickRef.current?.restoreFocus();
    };

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { mutate } = useFileManagerSwr();
    const { clearAndAddHttpError, clearFlashes } = useFlash();
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const removeSelectedFile = ServerContext.useStoreActions((actions) => actions.files.removeSelectedFile);
    const isMultiSelected = ServerContext.useStoreState(
        (state) => state.files.selectedFiles.length > 1 && state.files.selectedFiles.includes(file.name),
    );
    // These actions are unavailable while the mass actions bar is still working, both for the files it is
    // working on and for any other multi-selection, since it can only run one action at a time.
    const massActionRunning = ServerContext.useStoreState(
        (state) =>
            state.files.massActionFiles.includes(join(state.files.directory, file.name)) ||
            (state.files.massActionFiles.length > 0 && isMultiSelected),
    );

    const massAction = (detail: MassAction) => window.dispatchEvent(new CustomEvent(MASS_ACTION_EVENT, { detail }));

    useEventListener(`panel:files:ctx:${file.key}`, (e: CustomEvent) => {
        if (onClickRef.current) {
            archiveHovered.current = false;
            setShowFormatMenu(false);
            onClickRef.current.triggerMenu(e.detail);
        }
    });

    const doDeletion = () => {
        clearFlashes('files');

        // For UI speed, immediately remove the file from the listing before calling the deletion function.
        // If the delete actually fails, we'll fetch the current directory contents again automatically.
        mutate((files) => files?.filter((f) => f.key !== file.key), false);

        deleteFiles(uuid, directory, [file.name])
            .then(() => removeSelectedFile(file.name))
            .catch((error) => {
                mutate();
                clearAndAddHttpError({ key: 'files', error });
            });
    };

    const doCopy = () => {
        setShowSpinner(true);
        clearFlashes('files');

        copyFile(uuid, join(directory, file.name))
            .then(() => mutate())
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setShowSpinner(false));
    };

    const doDownload = () => {
        setShowSpinner(true);
        clearFlashes('files');

        getFileDownloadUrl(uuid, join(directory, file.name))
            .then((url) => {
                window.location.href = url;
            })
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setShowSpinner(false));
    };

    const archiveFormat = useStoreState((state) => state.user.data!.archiveFormat);

    const positionFormatMenu = () => {
        setShowFormatMenu(true);
        requestAnimationFrame(() => {
            const item = archiveItemRef.current;
            const menu = formatMenuRef.current;
            if (!item || !menu) return;

            const itemRect = item.getBoundingClientRect();
            const menuWidth = menu.offsetWidth;
            const menuHeight = menu.offsetHeight;
            const gap = 8;
            const narrow = window.innerWidth < 640;
            const openLeft = !narrow && itemRect.right + menuWidth > window.innerWidth - gap;
            const left = narrow
                ? Math.max(gap, Math.min(itemRect.left, window.innerWidth - menuWidth - gap))
                : openLeft
                  ? Math.max(gap, itemRect.left - menuWidth + gap)
                  : itemRect.right - gap;
            const top = narrow
                ? Math.max(gap, Math.min(itemRect.bottom, window.innerHeight - menuHeight - gap))
                : Math.max(gap, Math.min(itemRect.top, window.innerHeight - menuHeight - gap));
            setFormatMenuPosition({ position: 'fixed', left, top });
        });
    };

    const doArchive = (format: 'tar.gz' | 'zip') => {
        if (isMultiSelected) {
            massAction({ type: 'archive', format });
            return;
        }

        setShowSpinner(true);
        clearFlashes('files');

        compressFiles(uuid, directory, [file.name], format)
            .then(() => mutate())
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setShowSpinner(false));
    };

    const doUnarchive = () => {
        setShowSpinner(true);
        clearFlashes('files');

        decompressFiles(uuid, directory, file.name)
            .then(() => mutate())
            .catch((error) => clearAndAddHttpError({ key: 'files', error }))
            .then(() => setShowSpinner(false));
    };

    return (
        <>
            <Dialog.Confirm
                open={showConfirmation}
                onClose={() => {
                    setShowConfirmation(false);
                    onClickRef.current?.restoreFocus();
                }}
                title={`${t(file.isFile ? 'dropdown.delete-file' : 'dropdown.delete-directory')}`}
                confirm={t('dropdown.delete')}
                onConfirmed={doDeletion}
            >
                {t('dropdown.delete-confirm-message')}&nbsp;
                <span className={'font-semibold text-gray-50'}>{file.name}</span>{' '}
                {t('dropdown.delete-confirm-once-deleted')}.
            </Dialog.Confirm>
            {modal === 'chmod' && (
                <ChmodFileModal
                    visible
                    appear
                    files={[{ file: file.name, mode: file.modeBits }]}
                    onDismissed={dismissModal}
                />
            )}
            {(modal === 'rename' || modal === 'move') && (
                <>
                    {modal === 'rename' && (
                        <RenameFileModal visible appear files={[file.name]} onDismissed={dismissModal} />
                    )}
                    {modal === 'move' && (
                        <MoveFileModal
                            visible
                            appear
                            files={[file.name]}
                            directoryNames={file.isFile ? [] : [file.name]}
                            onDismissed={dismissModal}
                        />
                    )}
                </>
            )}
            <SpinnerOverlay visible={showSpinner} fixed size={'large'} />
            <DropdownMenu
                ref={onClickRef}
                id={menuId}
                manageFocus
                renderToggle={(onClick, visible) => (
                    <button
                        type='button'
                        aria-label={`${t('strings:action')}: ${file.name}`}
                        aria-expanded={visible}
                        aria-controls={menuId}
                        className='rounded-ui px-4 py-2 hover:text-white focus-visible:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-400'
                        onClick={(event) => {
                            archiveHovered.current = false;
                            setShowFormatMenu(false);
                            onClick(event);
                        }}
                    >
                        <FaEllipsis />
                    </button>
                )}
            >
                <ExtensionSlot name='server:files:dropdown:start' />
                <Can action={'file.update'}>
                    <Row onClick={() => setModal('rename')} icon={FaPen} title={t('dropdown.rename')} />
                    <Row
                        onClick={() => (isMultiSelected ? massAction({ type: 'move' }) : setModal('move'))}
                        icon={FaTurnUp}
                        title={t('dropdown.move')}
                        disabled={massActionRunning}
                    />
                    <Row onClick={() => setModal('chmod')} icon={FaFileCode} title={t('dropdown.permissions')} />
                </Can>
                {file.isFile && (
                    <Can action={'file.create'}>
                        <Row onClick={doCopy} icon={FaCopy} title={t('dropdown.copy')} />
                    </Can>
                )}
                {file.isArchiveType() ? (
                    <Can action={'file.create'}>
                        <Row onClick={doUnarchive} icon={FaBoxOpen} title={t('dropdown.unarchive')} />
                    </Can>
                ) : (
                    <Can action={'file.archive'}>
                        <div
                            ref={archiveItemRef}
                            className={`relative ${massActionRunning ? 'pointer-events-none opacity-50' : ''}`}
                            onMouseEnter={() => {
                                archiveHovered.current = true;
                                positionFormatMenu();
                            }}
                            onMouseLeave={(event) => {
                                archiveHovered.current = false;
                                if (!event.currentTarget.contains(document.activeElement)) setShowFormatMenu(false);
                            }}
                            onFocusCapture={positionFormatMenu}
                            onBlur={(event) => {
                                if (!archiveHovered.current && !event.currentTarget.contains(event.relatedTarget)) {
                                    setShowFormatMenu(false);
                                }
                            }}
                        >
                            <button
                                type='button'
                                onClick={(event) => {
                                    if (window.matchMedia('(hover: none), (max-width: 639px)').matches) {
                                        event.stopPropagation();
                                        positionFormatMenu();
                                        return;
                                    }
                                    doArchive(archiveFormat);
                                }}
                                aria-expanded={showFormatMenu}
                                aria-controls={`${menuId}-formats`}
                                disabled={massActionRunning}
                                className='flex w-full items-center rounded-ui p-2 text-left transition-all duration-150 hover:bg-gray-100 hover:text-gray-800 focus:bg-gray-100 focus:text-gray-800'
                            >
                                <FaFileZipper className='text-xs inline-block w-[1.25em]' />
                                <span className='ml-2'>{t('dropdown.archive')}</span>
                                <FaChevronRight className='ml-auto text-xs' aria-hidden />
                            </button>
                            <div
                                id={`${menuId}-formats`}
                                role='group'
                                aria-label={t('dropdown.archive')}
                                ref={formatMenuRef}
                                style={{ ...formatMenuPosition, display: showFormatMenu ? 'block' : 'none' }}
                                className='absolute left-[calc(100%-0.5rem)] -top-2 z-30 w-48 rounded-ui border border-gray-800 bg-gray-800 p-2 text-gray-100 shadow-lg'
                            >
                                {(['tar.gz', 'zip'] as const).map((format) => (
                                    <button
                                        key={format}
                                        type='button'
                                        onClick={() => doArchive(format)}
                                        disabled={massActionRunning}
                                        aria-label={t('archive-as', { format })}
                                        className='block w-full rounded-ui p-2 text-left text-sm transition-colors duration-150 hover:bg-gray-100 hover:text-gray-800 focus:bg-gray-100 focus:text-gray-800'
                                    >
                                        {format}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </Can>
                )}
                {file.isFile && (
                    <Can action={'file.read-content'}>
                        <Row onClick={doDownload} icon={FaFileArrowDown} title={t('dropdown.download')} />
                    </Can>
                )}
                <Can action={'file.delete'}>
                    <Row
                        onClick={() => (isMultiSelected ? massAction({ type: 'delete' }) : setShowConfirmation(true))}
                        icon={FaTrash}
                        title={t('dropdown.delete')}
                        danger
                        disabled={massActionRunning}
                    />
                </Can>
                <ExtensionSlot name='server:files:dropdown:end' />
            </DropdownMenu>
        </>
    );
};

export default memo(FileDropdownMenu, isEqual);
