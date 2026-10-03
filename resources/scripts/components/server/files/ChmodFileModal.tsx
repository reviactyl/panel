import { fileBitsToString } from '@/helpers';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import FileModeInput, { isValidFileMode } from './FileModeInput';
import chmodFiles from '@/api/server/files/chmodFiles';
import { ServerContext } from '@/state/server';
import Button from '@/reviactyl/elements/Button';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import useFlash from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';

interface FormikValues {
    mode: string;
}

interface File {
    file: string;
    mode: string;
}

type OwnProps = RequiredModalProps & { files: File[] };

const ChmodFileModal = ({ files, ...props }: OwnProps) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { t } = useTranslation('server/files');
    const { mutate } = useFileManagerSwr();
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);

    const submit = async ({ mode }: FormikValues, { setSubmitting }: FormikHelpers<FormikValues>) => {
        clearFlashes('files');

        await mutate(
            (data) =>
                data!.map((f) =>
                    f.name === files[0]?.file ? { ...f, mode: fileBitsToString(mode, !f.isFile), modeBits: mode } : f,
                ),
            false,
        );

        const data = files.map((f) => ({ file: f.file, mode: mode }));

        chmodFiles(uuid, directory, data)
            .then((): Promise<any> => (files.length > 0 ? mutate() : Promise.resolve()))
            .then(() => setSelectedFiles([]))
            .catch((error) => {
                mutate();
                setSubmitting(false);
                clearAndAddHttpError({ key: 'files', error });
            })
            .then(() => props.onDismissed());
    };

    return (
        <Formik
            onSubmit={submit}
            initialValues={{ mode: files.length > 1 ? '' : (files[0]?.mode ?? '') }}
            validate={({ mode }) => (isValidFileMode(mode) ? {} : { mode: t('permissions.invalid') })}
        >
            {({ isSubmitting, values }) => (
                <Modal {...props} dismissable={!isSubmitting} showSpinnerOverlay={isSubmitting}>
                    <Form className='m-0'>
                        <FileModeInput />
                        <div className='mt-6 flex justify-end'>
                            <Tooltip
                                content={t('permissions.invalid')}
                                disabled={isValidFileMode(values.mode)}
                                rest={150}
                                delay={{ open: 0, close: 100 }}
                            >
                                <span
                                    className='inline-flex rounded-ui focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-400'
                                    tabIndex={isValidFileMode(values.mode) ? undefined : 0}
                                    aria-label={isValidFileMode(values.mode) ? undefined : t('permissions.invalid')}
                                >
                                    <Button
                                        type='submit'
                                        className={!isValidFileMode(values.mode) ? 'pointer-events-none' : undefined}
                                        disabled={isSubmitting || !isValidFileMode(values.mode)}
                                    >
                                        {t('update')}
                                    </Button>
                                </span>
                            </Tooltip>
                        </div>
                    </Form>
                </Modal>
            )}
        </Formik>
    );
};

export default ChmodFileModal;
