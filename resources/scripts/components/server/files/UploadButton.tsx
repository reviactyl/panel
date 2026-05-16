import axios, { AxiosProgressEvent } from 'axios';
import getFileUploadUrl from '@/api/server/files/getFileUploadUrl';
import tw from 'twin.macro';
import { Button } from '@/reviactyl/elements/button/index';
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
import { FaUpload } from 'react-icons/fa6';

function isFileOrDirectory(event: DragEvent): boolean {
    if (!event.dataTransfer?.types) {
        return false;
    }

    return event.dataTransfer.types.some((value) => value.toLowerCase() === 'files');
}

export default ({ className }: WithClassname & { compact?: boolean }) => {
    const fileUploadInput = useRef<HTMLInputElement>(null);

    const [visible, setVisible] = useState(false);
    const visibleRef = useRef(false);
    const timeouts = useRef<NodeJS.Timeout[]>([]);
    const dragCounter = useRef(0);
    const onFileSubmissionRef = useRef<((files: FileList) => void) | null>(null);

    const { mutate } = useFileManagerSwr();
    const { addError, clearAndAddHttpError } = useFlashKey('files');

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { clearFileUploads, removeFileUpload, pushFileUpload, setUploadProgress } = ServerContext.useStoreActions(
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

            if (wasVisible && e.dataTransfer?.files?.length) {
                onFileSubmissionRef.current?.(e.dataTransfer.files);
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
        return () => timeouts.current.forEach(clearTimeout);
    }, []);

    const onUploadProgress = (data: AxiosProgressEvent, name: string) => {
        setUploadProgress({ name, loaded: data.loaded });
    };

    const onFileSubmission = (files: FileList) => {
        clearAndAddHttpError();
        const list = Array.from(files);
        if (list.some((file) => !file.type && (!file.size || file.size === 4096))) {
            return addError('Folder uploads are not supported.', 'Error');
        }

        const uploads = list.map((file) => {
            const controller = new AbortController();
            pushFileUpload({
                name: file.name,
                data: { abort: controller, loaded: 0, total: file.size },
            });

            return () =>
                getFileUploadUrl(uuid).then((url) =>
                    axios
                        .post(
                            url,
                            { files: file },
                            {
                                signal: controller.signal,
                                headers: { 'Content-Type': 'multipart/form-data' },
                                params: { directory },
                                onUploadProgress: (data) => onUploadProgress(data, file.name),
                            }
                        )
                        .then(() => timeouts.current.push(setTimeout(() => removeFileUpload(file.name), 500)))
                );
        });

        Promise.all(uploads.map((fn) => fn()))
            .then(() => mutate())
            .catch((error) => {
                clearFileUploads();
                clearAndAddHttpError(error);
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
                                    Drag and drop files to upload.
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

                    onFileSubmission(e.currentTarget.files);
                    if (fileUploadInput.current) {
                        fileUploadInput.current.files = null;
                    }
                }}
                multiple
            />
            <Tooltip content={'Upload'}>
                <Button.Text
                    className={className}
                    aria-label={'Upload'}
                    onClick={() => fileUploadInput.current && fileUploadInput.current.click()}
                >
                    <FaUpload className='h-5 w-5' />
                </Button.Text>
            </Tooltip>
        </>
    );
};
