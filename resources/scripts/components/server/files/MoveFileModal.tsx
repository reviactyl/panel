import { Fragment, useEffect, useMemo, useState } from 'react';
import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import tw from 'twin.macro';
import Button from '@/reviactyl/elements/Button';
import Input from '@/reviactyl/elements/Input';
import FlashMessageRender from '@/components/FlashMessageRender';
import { FaArrowLeft, FaFile, FaFolder, FaFolderPlus, FaSpinner } from 'react-icons/fa';
import { join, relative } from 'pathe';
import loadDirectory, { FileObject } from '@/api/server/files/loadDirectory';
import renameFiles from '@/api/server/files/renameFiles';
import createDirectory from '@/api/server/files/createDirectory';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import useFlash from '@/plugins/useFlash';
import { ServerContext } from '@/state/server';

interface Props extends RequiredModalProps {
    files: string[];
    directoryNames?: string[];
}

const toAbsolutePath = (value: string): string => {
    if (!value || value === '.') {
        return '/';
    }

    return value.startsWith('/') ? value : `/${value}`;
};

const getParentDirectory = (value: string): string => {
    if (!value || value === '/') {
        return '/';
    }

    const segments = value.split('/').filter(Boolean);
    segments.pop();

    return segments.length ? `/${segments.join('/')}` : '/';
};

const getDisplayPath = (value: string): string => {
    return value === '/' ? '/home/container' : `/home/container${value}`;
};

interface Breadcrumb {
    label: string;
    path: string;
}

const MoveFileModal = ({ files, directoryNames = [], ...props }: Props) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const sourceDirectory = toAbsolutePath(ServerContext.useStoreState((state) => state.files.directory || '/'));
    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);

    const [destinationDirectory, setDestinationDirectory] = useState(sourceDirectory);
    const [entries, setEntries] = useState<FileObject[]>([]);
    const [isLoadingEntries, setIsLoadingEntries] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [newFolderName, setNewFolderName] = useState('');
    const [isCreatingFolder, setIsCreatingFolder] = useState(false);

    const { mutate } = useFileManagerSwr();
    const { clearFlashes, clearAndAddHttpError } = useFlash();

    useEffect(() => {
        if (!props.visible) {
            return;
        }

        setDestinationDirectory(sourceDirectory);
        setNewFolderName('');
    }, [props.visible, sourceDirectory]);

    useEffect(() => {
        if (!props.visible) {
            return;
        }

        setIsLoadingEntries(true);
        clearFlashes('files:move-modal');

        loadDirectory(uuid, destinationDirectory)
            .then((data) => {
                setEntries(
                    [...data].sort((a, b) => {
                        if (a.isFile !== b.isFile) {
                            return a.isFile ? 1 : -1;
                        }

                        return a.name.localeCompare(b.name);
                    })
                );
            })
            .catch((error) => clearAndAddHttpError({ key: 'files:move-modal', error }))
            .then(() => setIsLoadingEntries(false));
    }, [props.visible, destinationDirectory, uuid]);

    const title = useMemo(() => {
        if (files.length === 1) {
            return `Move "${files[0]}"`;
        }

        return `Move ${files.length} item${files.length === 1 ? '' : 's'}`;
    }, [files]);

    const destinationBreadcrumbs = useMemo<Breadcrumb[]>(() => {
        const parts = destinationDirectory.split('/').filter(Boolean);
        const breadcrumbs: Breadcrumb[] = [{ label: '/home/container', path: '/' }];

        parts.forEach((part, index) => {
            breadcrumbs.push({
                label: part,
                path: `/${parts.slice(0, index + 1).join('/')}`,
            });
        });

        return breadcrumbs;
    }, [destinationDirectory]);

    const selfMoveBlocked = useMemo(() => {
        return directoryNames
            .map((name) => toAbsolutePath(join(sourceDirectory, name)))
            .some(
                (directoryPath) =>
                    destinationDirectory === directoryPath || destinationDirectory.startsWith(`${directoryPath}/`)
            );
    }, [directoryNames, sourceDirectory, destinationDirectory]);

    const canMove = destinationDirectory !== sourceDirectory && files.length > 0 && !isSubmitting && !selfMoveBlocked;

    const onMove = () => {
        if (!canMove) {
            return;
        }

        clearFlashes('files');
        setIsSubmitting(true);

        const destinationFromSource = relative(sourceDirectory, destinationDirectory) || '.';
        const payload = files.map((file) => ({ from: file, to: join(destinationFromSource, file) }));

        mutate((data) => data.filter((file) => !files.includes(file.name)), false);

        renameFiles(uuid, sourceDirectory, payload)
            .then(() => mutate())
            .then(() => setSelectedFiles([]))
            .then(() => props.onDismissed())
            .catch((error) => {
                mutate();
                setIsSubmitting(false);
                clearAndAddHttpError({ key: 'files', error });
            });
    };

    const onCreateFolder = () => {
        const folderName = newFolderName.trim();
        if (!folderName || isCreatingFolder) {
            return;
        }

        clearFlashes('files:move-modal');
        setIsCreatingFolder(true);

        createDirectory(uuid, destinationDirectory, folderName)
            .then(() => {
                const nextDirectory = toAbsolutePath(join(destinationDirectory, folderName));
                setDestinationDirectory(nextDirectory);
                setNewFolderName('');
            })
            .catch((error) => clearAndAddHttpError({ key: 'files:move-modal', error }))
            .then(() => setIsCreatingFolder(false));
    };

    return (
        <Modal
            {...props}
            dismissable={!isSubmitting && !isCreatingFolder}
            showSpinnerOverlay={isSubmitting}
            size={'lg'}
        >
            <div css={tw`space-y-5`}>
                <div>
                    <h2 css={tw`text-2xl font-semibold text-gray-50`}>{title}</h2>
                    <p css={tw`text-sm text-gray-300 mt-1`}>Current location: {getDisplayPath(sourceDirectory)}</p>
                    <p css={tw`text-sm text-gray-300 mt-1`}>Destination: {getDisplayPath(destinationDirectory)}</p>
                </div>

                <FlashMessageRender key={'files:move-modal'} />

                <div css={tw`border border-gray-500 rounded-ui overflow-hidden bg-gray-600/40`}>
                    <div
                        css={tw`flex items-center justify-between gap-3 px-3 py-2 border-b border-gray-500 bg-gray-600/60`}
                    >
                        <button
                            type={'button'}
                            css={tw`text-xs font-semibold text-gray-200 hover:text-gray-50 disabled:opacity-50 disabled:cursor-not-allowed`}
                            onClick={() => setDestinationDirectory((current) => getParentDirectory(current))}
                            disabled={destinationDirectory === '/' || isLoadingEntries}
                        >
                            <span css={tw`inline-flex items-center gap-2`}>
                                <FaArrowLeft />
                                Back
                            </span>
                        </button>
                        <div css={tw`min-w-0 text-xs text-gray-300 flex items-center gap-1 overflow-x-auto`}>
                            {destinationBreadcrumbs.map((breadcrumb, index) => (
                                <Fragment key={breadcrumb.path}>
                                    {index > 0 && <span css={tw`text-gray-500`}>/</span>}
                                    <button
                                        type={'button'}
                                        css={tw`text-gray-200 hover:text-gray-50 whitespace-nowrap`}
                                        onClick={() => setDestinationDirectory(breadcrumb.path)}
                                    >
                                        {breadcrumb.label}
                                    </button>
                                </Fragment>
                            ))}
                        </div>
                    </div>

                    <div css={tw`max-h-72 overflow-y-auto`}>
                        {isLoadingEntries ? (
                            <div css={tw`py-10 flex items-center justify-center text-gray-300 text-sm gap-2`}>
                                <FaSpinner className={'animate-spin'} />
                                Loading files...
                            </div>
                        ) : (
                            <>
                                {entries.length === 0 ? (
                                    <div css={tw`py-10 text-center text-sm text-gray-300`}>This folder is empty.</div>
                                ) : (
                                    entries.map((entry) =>
                                        entry.isFile ? (
                                            <div
                                                key={entry.key}
                                                css={tw`flex items-center gap-3 px-4 py-2 text-sm text-gray-400 border-b border-gray-500/70 last:border-b-0`}
                                            >
                                                <FaFile css={tw`text-gray-400`} />
                                                <span css={tw`truncate`}>{entry.name}</span>
                                            </div>
                                        ) : (
                                            <button
                                                key={entry.key}
                                                type={'button'}
                                                css={tw`w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-100 border-b border-gray-500/70 last:border-b-0 hover:bg-gray-500/50 transition-colors`}
                                                onClick={() =>
                                                    setDestinationDirectory(
                                                        toAbsolutePath(join(destinationDirectory, entry.name))
                                                    )
                                                }
                                            >
                                                <FaFolder css={tw`text-yellow-300`} />
                                                <span css={tw`truncate text-left`}>{entry.name}</span>
                                            </button>
                                        )
                                    )
                                )}
                            </>
                        )}
                    </div>
                </div>

                <div css={tw`flex flex-wrap items-end gap-2`}>
                    <div css={tw`flex-1 min-w-0`}>
                        <label
                            htmlFor={'new_move_folder_name'}
                            css={tw`block text-xs font-semibold text-gray-300 mb-1`}
                        >
                            New folder in current destination
                        </label>
                        <Input
                            id={'new_move_folder_name'}
                            type={'text'}
                            value={newFolderName}
                                                onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                                                    setNewFolderName(e.currentTarget.value)
                                                }
                            placeholder={'Folder name'}
                            disabled={isCreatingFolder || isSubmitting}
                        />
                    </div>
                    <Button
                        type={'button'}
                        css={tw`whitespace-nowrap`}
                        disabled={!newFolderName.trim() || isCreatingFolder || isSubmitting}
                        onClick={onCreateFolder}
                    >
                        <span css={tw`inline-flex items-center gap-2`}>
                            <FaFolderPlus />
                            {isCreatingFolder ? 'Creating...' : 'New folder'}
                        </span>
                    </Button>
                </div>

                <div css={tw`flex flex-wrap justify-end gap-3 pt-2`}>
                    {selfMoveBlocked && (
                        <p css={tw`w-full text-sm text-red-300`}>You cannot move a folder into itself.</p>
                    )}
                    <Button
                        type={'button'}
                        isSecondary
                        onClick={props.onDismissed}
                        disabled={isSubmitting || isCreatingFolder}
                    >
                        Cancel
                    </Button>
                    <Button type={'button'} onClick={onMove} disabled={!canMove}>
                        Move
                    </Button>
                </div>
            </div>
        </Modal>
    );
};

export default MoveFileModal;
