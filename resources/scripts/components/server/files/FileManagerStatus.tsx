import { useContext, useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import { XIcon } from '@heroicons/react/solid';
import asDialog from '@/hoc/asDialog';
import { Dialog, DialogWrapperContext } from '@/reviactyl/elements/dialog';
import { Button } from '@/reviactyl/components/button/index';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import Code from '@/reviactyl/elements/Code';
import { WithClassname } from '@/components/types';
import { FaCloudArrowDown } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import type { FileUploadData } from '@/state/server/files';

const svgProps = {
    cx: 16,
    cy: 16,
    r: 14,
    strokeWidth: 3,
    fill: 'none',
    stroke: 'currentColor',
};

const Spinner = ({ progress, className }: { progress: number; className?: string }) => (
    <svg viewBox={'0 0 32 32'} className={className}>
        <circle {...svgProps} className={'opacity-25'} />
        <circle
            {...svgProps}
            stroke={'white'}
            strokeDasharray={28 * Math.PI}
            className={'rotate-[-90deg] origin-[50%_50%] transition-[stroke-dashoffset] duration-300'}
            style={{ strokeDashoffset: ((100 - progress) / 100) * 28 * Math.PI }}
        />
    </svg>
);

interface UploadGroup {
    id: string;
    name: string;
    keys: string[];
    loaded: number;
    total: number;
}

const uploadProgress = ({ loaded, total }: Pick<UploadGroup, 'loaded' | 'total'>) =>
    total === 0 ? 100 : Math.min((loaded / total) * 100, 100);

const groupUploads = (uploads: Record<string, FileUploadData>): UploadGroup[] => {
    const groups = new Map<string, UploadGroup>();

    Object.entries(uploads).forEach(([key, upload]) => {
        const id = upload.group ? `group:${upload.group}` : `file:${key}`;
        const existing = groups.get(id);

        if (existing) {
            existing.keys.push(key);
            existing.loaded += upload.loaded;
            existing.total += upload.total;
        } else {
            groups.set(id, {
                id,
                name: upload.group || key,
                keys: [key],
                loaded: upload.loaded,
                total: upload.total,
            });
        }
    });

    return Array.from(groups.values()).sort((a, b) => a.name.localeCompare(b.name));
};

const FileUploadList = () => {
    const { t } = useTranslation('server/files');
    const { close, setProps } = useContext(DialogWrapperContext);

    useEffect(() => {
        setProps({ title: t('uploads-title'), description: t('uploads-description') });
    }, [setProps, t]);
    const cancelFileUpload = ServerContext.useStoreActions((actions) => actions.files.cancelFileUpload);
    const clearFileUploads = ServerContext.useStoreActions((actions) => actions.files.clearFileUploads);
    const uploads = ServerContext.useStoreState((state) => groupUploads(state.files.uploads));

    return (
        <div className={'space-y-2 mt-6'}>
            {uploads.map((upload) => (
                <div key={upload.id} className={'flex items-center space-x-3 bg-gray-900 p-3 rounded'}>
                    <Tooltip content={`${Math.floor(uploadProgress(upload))}%`} placement={'left'}>
                        <div className={'flex-shrink-0'}>
                            <Spinner progress={uploadProgress(upload)} className={'w-6 h-6'} />
                        </div>
                    </Tooltip>
                    <Code>{upload.name}</Code>
                    <button
                        onClick={() => upload.keys.forEach((key) => cancelFileUpload(key))}
                        className={'text-gray-600 hover:text-gray-200 transition-colors duration-75'}
                    >
                        <XIcon className={'w-5 h-5'} />
                    </button>
                </div>
            ))}
            <Dialog.Footer>
                <Button.Danger variant={Button.Variants.Secondary} onClick={() => clearFileUploads()}>
                    {t('cancel-uploads')}
                </Button.Danger>
                <Button.Text onClick={close}>{t('close')}</Button.Text>
            </Dialog.Footer>
        </div>
    );
};

const FileUploadListDialog = asDialog({})(FileUploadList);

export default ({ className }: WithClassname) => {
    const { t } = useTranslation('server/files');
    const [open, setOpen] = useState(false);

    const count = ServerContext.useStoreState((state) => groupUploads(state.files.uploads).length);
    const progress = ServerContext.useStoreState((state) => ({
        loaded: Object.values(state.files.uploads).reduce((count, file) => count + file.loaded, 0),
        total: Object.values(state.files.uploads).reduce((count, file) => count + file.total, 0),
    }));

    useEffect(() => {
        if (count === 0) {
            setOpen(false);
        }
    }, [count]);

    return (
        <>
            {count > 0 && (
                <Tooltip content={t('uploads-tooltip', { count })}>
                    <button
                        className={
                            className ||
                            'flex items-center justify-center w-10 h-10 rounded-ui bg-gray-900 border border-gray-800 text-blue-300 hover:text-blue-100 hover:border-gray-600 transition-colors'
                        }
                        onClick={() => setOpen(true)}
                    >
                        <Spinner progress={uploadProgress(progress)} className={'w-8 h-8'} />
                        <FaCloudArrowDown className={'h-3 absolute mx-auto animate-pulse'} />
                    </button>
                </Tooltip>
            )}
            <FileUploadListDialog open={open && count > 0} onClose={() => setOpen(false)} />
        </>
    );
};
