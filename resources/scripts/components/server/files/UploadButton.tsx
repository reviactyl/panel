import axios, { AxiosProgressEvent } from 'axios';
import getFileUploadUrl from '@/api/server/files/getFileUploadUrl';
import tw from 'twin.macro';
import { useEffect, useRef, useState } from 'react';
import { ModalMask } from '@/reviactyl/elements/Modal';
import Fade from '@/reviactyl/elements/Fade';
import useEventListener from '@/plugins/useEventListener';
import { useFlashKey } from '@/plugins/useFlash';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { ServerContext } from '@/state/server';
import { WithClassname } from '@/components/types';
import Portal from '@/reviactyl/elements/Portal';
import Card from '@/reviactyl/ui/Card';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';
import http from '@/api/http';
import i18n from '@/i18n';
import { bytesToString } from '@/lib/formatters';
import { FaFileArrowUp, FaFolderOpen, FaUpload } from 'react-icons/fa6';
import { dirname, join } from 'pathe';
import { useTranslation } from 'react-i18next';
import { Button } from '@/reviactyl/components/button';
import { DropdownButtonRow } from '@/reviactyl/elements/DropdownMenu';
import {
    autoUpdate,
    flip,
    FloatingPortal,
    offset,
    shift,
    useClick,
    useDismiss,
    useFloating,
    useInteractions,
    useRole,
} from '@floating-ui/react';

interface UploadFile {
    file: File;
    path: string;
    group?: string;
}

const MAX_PARALLEL_UPLOADS = 4;

const normalizeRelativePath = (path: string): string =>
    path
        .replaceAll('\\', '/')
        .split('/')
        .filter((part) => part && part !== '.' && part !== '..')
        .join('/');

const filesFromList = (files: FileList, groupFolders = false): UploadFile[] =>
    Array.from(files).map((file) => {
        const path = normalizeRelativePath(file.webkitRelativePath || file.name);

        return {
            file,
            path,
            group: groupFolders && path.includes('/') ? path.split('/')[0] : undefined,
        };
    });

const readDirectoryEntries = async (directory: FileSystemDirectoryEntry): Promise<FileSystemEntry[]> => {
    const reader = directory.createReader();
    const entries: FileSystemEntry[] = [];

    while (true) {
        const batch = await new Promise<FileSystemEntry[]>((resolve, reject) => reader.readEntries(resolve, reject));
        if (batch.length === 0) return entries;

        entries.push(...batch);
    }
};

const filesFromEntry = async (entry: FileSystemEntry, group?: string): Promise<UploadFile[]> => {
    if (entry.isFile) {
        const file = await new Promise<File>((resolve, reject) => (entry as FileSystemFileEntry).file(resolve, reject));

        return [{ file, path: normalizeRelativePath(entry.fullPath || file.name), group }];
    }

    if (!entry.isDirectory) return [];

    const entries = await readDirectoryEntries(entry as FileSystemDirectoryEntry);
    return (await Promise.all(entries.map((child) => filesFromEntry(child, group)))).flat();
};

const filesFromDataTransfer = async (dataTransfer: DataTransfer): Promise<UploadFile[]> => {
    const entries = Array.from(dataTransfer.items)
        .filter((item) => item.kind === 'file')
        .map((item) => item.webkitGetAsEntry?.())
        .filter((entry): entry is FileSystemEntry => !!entry);

    if (entries.length > 0) {
        return (
            await Promise.all(
                entries.map((entry) =>
                    filesFromEntry(entry, entry.isDirectory ? normalizeRelativePath(entry.name) : undefined)
                )
            )
        ).flat();
    }

    return filesFromList(dataTransfer.files);
};

const settleUploads = async (uploads: Array<() => Promise<void>>): Promise<PromiseSettledResult<void>[]> => {
    const results: PromiseSettledResult<void>[] = new Array(uploads.length);
    let next = 0;

    const worker = async () => {
        while (next < uploads.length) {
            const index = next++;
            const upload = uploads[index];
            if (!upload) return;

            try {
                await upload();
                results[index] = { status: 'fulfilled', value: undefined };
            } catch (reason) {
                results[index] = { status: 'rejected', reason };
            }
        }
    };

    await Promise.all(Array.from({ length: Math.min(MAX_PARALLEL_UPLOADS, uploads.length) }, worker));

    return results;
};

interface UploadMenuProps {
    className?: string;
    onFiles: () => void;
    onFolder: () => void;
    uploadLabel: string;
    filesLabel: string;
    folderLabel: string;
}

const UploadMenu = ({ className, onFiles, onFolder, uploadLabel, filesLabel, folderLabel }: UploadMenuProps) => {
    const [open, setOpen] = useState(false);
    const { refs, floatingStyles, context } = useFloating({
        open,
        onOpenChange: setOpen,
        placement: 'bottom-start',
        strategy: 'fixed',
        middleware: [offset(6), flip({ padding: 8 }), shift({ padding: 8 })],
        whileElementsMounted: autoUpdate,
    });
    const { getReferenceProps, getFloatingProps } = useInteractions([
        useClick(context),
        useDismiss(context),
        useRole(context, { role: 'menu' }),
    ]);

    const select = (action: () => void) => {
        setOpen(false);
        action();
    };

    return (
        <>
            <Tooltip content={uploadLabel}>
                <span className='inline-flex'>
                    <Button.Text
                        {...getReferenceProps({ ref: refs.setReference })}
                        className={className}
                        aria-label={uploadLabel}
                        aria-expanded={open}
                    >
                        <FaUpload className='h-5 w-5' />
                    </Button.Text>
                </span>
            </Tooltip>
            <FloatingPortal>
                {open && (
                    <div
                        {...getFloatingProps({ ref: refs.setFloating })}
                        style={{ ...floatingStyles, width: '12rem' }}
                        className='bg-gray-800 p-2 rounded-ui border border-gray-800 shadow-lg text-gray-100 z-50'
                    >
                        <DropdownButtonRow type='button' onClick={() => select(onFiles)}>
                            <FaFileArrowUp className='h-4 w-4 mr-3' />
                            {filesLabel}
                        </DropdownButtonRow>
                        <DropdownButtonRow type='button' onClick={() => select(onFolder)}>
                            <FaFolderOpen className='h-4 w-4 mr-3' />
                            {folderLabel}
                        </DropdownButtonRow>
                    </div>
                )}
            </FloatingPortal>
        </>
    );
};

/**
 * Determines whether a drag event contains file data.
 *
 * @param event - The drag event to inspect
 * @returns `true` if the event contains file data, `false` otherwise.
 */
function isFileOrDirectory(event: DragEvent): boolean {
    if (!event.dataTransfer?.types) {
        return false;
    }

    return event.dataTransfer.types.some((value) => value.toLowerCase() === 'files');
}

export default ({ className }: WithClassname & { compact?: boolean }) => {
    const fileUploadInput = useRef<HTMLInputElement>(null);
    const folderUploadInput = useRef<HTMLInputElement>(null);
    const { t } = useTranslation('server/files');

    const [visible, setVisible] = useState(false);
    const visibleRef = useRef(false);
    const timeouts = useRef<NodeJS.Timeout[]>([]);
    const dragCounter = useRef(0);
    const onFileSubmissionRef = useRef<((files: UploadFile[]) => void) | null>(null);

    const { mutate } = useFileManagerSwr();
    const { addError, clearAndAddHttpError } = useFlashKey('files');

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { session } = useSubuserPreview();
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { removeFileUpload, pushFileUpload, setUploadProgress } = ServerContext.useStoreActions(
        (actions) => actions.files
    );

    useEventListener(
        'dragenter',
        (e) => {
            e.preventDefault();
            e.stopPropagation();
            dragCounter.current++;
            if (dragCounter.current === 1 && isFileOrDirectory(e)) {
                visibleRef.current = true;
                setVisible(true);
            }
        },
        { capture: true }
    );

    useEventListener(
        'dragleave',
        (e) => {
            e.preventDefault();
            dragCounter.current = Math.max(dragCounter.current - 1, 0);
            if (dragCounter.current === 0) {
                visibleRef.current = false;
                setVisible(false);
            }
        },
        { capture: true }
    );

    useEventListener('dragover', (e) => e.preventDefault(), { capture: true });

    useEventListener(
        'drop',
        (e) => {
            e.preventDefault();
            const wasVisible = visibleRef.current;
            dragCounter.current = 0;
            visibleRef.current = false;
            setVisible(false);

            if (wasVisible && e.dataTransfer) {
                void filesFromDataTransfer(e.dataTransfer)
                    .then((files) => onFileSubmissionRef.current?.(files))
                    .catch((error) => clearAndAddHttpError(error));
            }
        },
        { capture: true }
    );

    useEventListener('keydown', () => {
        dragCounter.current = 0;
        visibleRef.current = false;
        setVisible(false);
    });

    useEffect(() => {
        folderUploadInput.current?.setAttribute('webkitdirectory', '');

        return () => timeouts.current.forEach(clearTimeout);
    }, []);

    const onUploadProgress = (data: AxiosProgressEvent, name: string) => {
        setUploadProgress({ name, loaded: data.loaded });
    };

    const onFileSubmission = (files: UploadFile[]) => {
        clearAndAddHttpError();
        if (files.length === 0) return addError(t('upload-empty-folder'), t('upload-error'));

        const uploads = files.map(({ file, path, group }) => {
            const controller = new AbortController();
            const destination = join(directory, path);
            const uploadDirectory = dirname(destination);

            pushFileUpload({
                name: path,
                data: { abort: controller, loaded: 0, total: file.size, group },
            });

            return () =>
                (session && session.maxFileSize > 0 && file.size > session.maxFileSize
                    ? Promise.reject(
                          new Error(
                              i18n.t('preview.file-too-large', {
                                  ns: 'server/users',
                                  size: bytesToString(session.maxFileSize),
                              })
                          )
                      )
                    : session
                    ? file.arrayBuffer().then((content) =>
                          http.post(`/api/client/servers/${uuid}/files/write`, content, {
                              params: { file: destination },
                              headers: { 'Content-Type': 'application/octet-stream' },
                              signal: controller.signal,
                          })
                      )
                    : getFileUploadUrl(uuid).then((url) =>
                          axios.post(
                              url,
                              { files: file },
                              {
                                  signal: controller.signal,
                                  headers: { 'Content-Type': 'multipart/form-data' },
                                  params: { directory: uploadDirectory },
                                  onUploadProgress: (data) => onUploadProgress(data, path),
                              }
                          )
                      )
                )
                    .then(() => setUploadProgress({ name: path, loaded: file.size }))
                    .then(() => {
                        if (!group) {
                            timeouts.current.push(setTimeout(() => removeFileUpload(path), 500));
                        }
                    })
                    .catch((error) => {
                        removeFileUpload(path);
                        if (!axios.isCancel(error)) {
                            throw error;
                        }
                    });
        });

        settleUploads(uploads).then((results) => {
            mutate();

            files.forEach(({ path, group }, index) => {
                if (group && results[index]?.status === 'fulfilled') {
                    timeouts.current.push(setTimeout(() => removeFileUpload(path), 500));
                }
            });

            const failure = results.find((result) => result.status === 'rejected');
            if (failure) {
                clearAndAddHttpError((failure as PromiseRejectedResult).reason);
            }
        });
    };

    // Keep ref in sync so the global drop handler can call the latest onFileSubmission
    onFileSubmissionRef.current = onFileSubmission;

    return (
        <>
            <Portal>
                <Fade appear in={visible} timeout={75} key={'upload_modal_mask'} unmountOnExit>
                    <ModalMask
                        onClick={() => {
                            dragCounter.current = 0;
                            visibleRef.current = false;
                            setVisible(false);
                        }}
                        className='bg-gray-900/40 backdrop-blur-sm transition-all duration-300 ease-in-out'
                    >
                        <div className={'w-full flex items-center justify-center pointer-events-none'}>
                            <Card
                                className={
                                    'flex items-center space-x-4 w-full ring-4 ring-gray-700 ring-opacity-60 p-6 mx-10 max-w-sm'
                                }
                            >
                                <FaUpload className={'w-10 h-10 flex-shrink-0'} />
                                <p className={'font-header flex-1 text-lg text-gray-100 text-center'}>
                                    {t('drop-files-folders')}
                                </p>
                            </Card>
                        </div>
                    </ModalMask>
                </Fade>
            </Portal>
            <input
                type={'file'}
                ref={fileUploadInput}
                css={tw`hidden`}
                onChange={(e) => {
                    if (!e.currentTarget.files) return;

                    onFileSubmission(filesFromList(e.currentTarget.files));
                    e.currentTarget.value = '';
                }}
                multiple
            />
            <input
                type={'file'}
                ref={folderUploadInput}
                css={tw`hidden`}
                onChange={(e) => {
                    if (!e.currentTarget.files) return;

                    onFileSubmission(filesFromList(e.currentTarget.files, true));
                    e.currentTarget.value = '';
                }}
                multiple
            />
            <UploadMenu
                className={className}
                uploadLabel={t('upload')}
                filesLabel={t('upload-files')}
                folderLabel={t('upload-folder')}
                onFiles={() => fileUploadInput.current?.click()}
                onFolder={() => folderUploadInput.current?.click()}
            />
        </>
    );
};
