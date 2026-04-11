import { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/components/elements/Field';
import { object, string } from 'yup';
import pullFile from '@/api/server/files/pullFile';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import { useFlashKey } from '@/plugins/useFlash';
import { WithClassname } from '@/components/types';
import FlashMessageRender from '@/components/FlashMessageRender';
import { Dialog } from '@/components/elements/dialog';
import { useTranslation } from 'react-i18next';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import { CloudDownloadIcon } from '@heroicons/react/solid';

interface Values {
    url: string;
}

const urlSchema = object().shape({
    url: string()
        .required('A URL is required.')
        .test('https-only', 'Only HTTPS URLs are allowed.', (value) => {
            if (!value) return false;
            try {
                return new URL(value).protocol === 'https:';
            } catch {
                return false;
            }
        })
        .test('no-command-injection', 'The URL contains invalid characters (no shell commands).', (value) => {
            if (!value) return false;
            return !/(\$\(|\$\{|`[^`]*`)/.test(value);
        })
        .url('Please enter a valid URL.'),
});

const svgProps = { cx: 16, cy: 16, r: 14, strokeWidth: 3, fill: 'none', stroke: 'currentColor' };
const DownloadSpinner = ({ className }: { className?: string }) => (
    <svg viewBox={'0 0 32 32'} className={className}>
        <circle {...svgProps} className={'opacity-25'} />
        <circle
            {...svgProps}
            stroke={'white'}
            strokeDasharray={28 * Math.PI}
            strokeLinecap={'round'}
            style={{ strokeDashoffset: 28 * Math.PI * 0.25 }}
            className={'origin-[50%_50%] animate-spin'}
        />
    </svg>
);

const extractFilename = (url: string): string | null => {
    try {
        const parts = new URL(url).pathname.split('/').filter(Boolean);
        return parts[parts.length - 1] || null;
    } catch {
        return null;
    }
};

export default ({ className }: WithClassname & { compact?: boolean }) => {
    const { t } = useTranslation('server/files');
    const [open, setOpen] = useState(false);
    const [downloading, setDownloading] = useState(false);
    const [downloadingFile, setDownloadingFile] = useState<string | null>(null);

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { mutate } = useFileManagerSwr();
    const { clearAndAddHttpError, clearFlashes } = useFlashKey('files:url-download-modal');

    useEffect(() => {
        if (!downloading || !downloadingFile) return;

        let active = true;
        const poll = setInterval(async () => {
            const files = await mutate();
            if (!active) return;
            if (files?.some((f) => f.name === downloadingFile)) {
                setDownloading(false);
                setDownloadingFile(null);
            }
        }, 2000);

        const giveUp = setTimeout(() => {
            if (!active) return;
            setDownloading(false);
            setDownloadingFile(null);
        }, 60000);

        return () => {
            active = false;
            clearInterval(poll);
            clearTimeout(giveUp);
        };
    }, [downloading, downloadingFile]);

    const submit = ({ url }: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();
        const filename = extractFilename(url.trim());
        pullFile(uuid, url.trim(), directory)
            .then(() => {
                setOpen(false);
                setDownloadingFile(filename);
                setDownloading(true);
            })
            .catch((error) => {
                setSubmitting(false);
                clearAndAddHttpError(error);
            });
    };

    return (
        <>
            {downloading && (
                <Tooltip content={`Downloading${downloadingFile ? ` ${downloadingFile}` : ''}...`}>
                    <button
                        className={
                            className ||
                            'relative flex items-center justify-center w-10 h-10 rounded-ui bg-gray-700 border border-gray-600 text-gray-300'
                        }
                    >
                        <DownloadSpinner className={'w-8 h-8'} />
                        <CloudDownloadIcon className={'h-3 absolute mx-auto animate-pulse'} />
                    </button>
                </Tooltip>
            )}
            <Dialog open={open} onClose={() => setOpen(false)} title={t('url-download.button')}>
                <FlashMessageRender key={'files:url-download-modal'} />
                <Formik
                    onSubmit={submit}
                    validationSchema={urlSchema}
                    validateOnChange={false}
                    validateOnBlur={false}
                    initialValues={{ url: '' }}
                >
                    {({ submitForm, isSubmitting }) => (
                        <>
                            <Form css={tw`m-0`}>
                                <p className={'mb-3 text-sm text-gray-400'}>{t('url-download.url-description')}</p>
                                <Field
                                    autoFocus
                                    id={'url'}
                                    name={'url'}
                                    label={t('url-download.url-label')}
                                    placeholder={'https://example.com/file.txt'}
                                />
                            </Form>
                            <Dialog.Footer>
                                <Button.Text className={'w-full sm:w-auto'} onClick={() => setOpen(false)}>
                                    {t('url-download.cancel')}
                                </Button.Text>
                                <Button className={'w-full sm:w-auto'} onClick={submitForm} disabled={isSubmitting}>
                                    {t('url-download.download')}
                                </Button>
                            </Dialog.Footer>
                        </>
                    )}
                </Formik>
            </Dialog>
            <Tooltip content={t('url-download.button')}>
                <Button.Text onClick={() => setOpen(true)} className={className} aria-label={t('url-download.button')}>
                    <CloudDownloadIcon className='h-5 w-5' />
                </Button.Text>
            </Tooltip>
        </>
    );
};
