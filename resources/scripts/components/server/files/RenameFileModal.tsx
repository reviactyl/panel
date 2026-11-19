import { useEffect } from 'react';
import FlashMessageRender from '@/components/FlashMessageRender';
import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/reviactyl/elements/Field';
import { join } from 'pathe';
import renameFiles from '@/api/server/files/renameFiles';
import { ServerContext } from '@/state/server';
import Button from '@/reviactyl/elements/Button';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import useFlash from '@/plugins/useFlash';
import { useTranslation } from 'react-i18next';
import clsx from 'clsx';

interface FormikValues {
    name: string;
}

type OwnProps = RequiredModalProps & {
    files: string[];
    useMoveTerminology?: boolean;
};

const RenameFileModal = ({ files, useMoveTerminology, ...props }: OwnProps) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);

    const { t } = useTranslation('server/files');

    const { mutate } = useFileManagerSwr();

    const { clearFlashes, clearAndAddHttpError } = useFlash();

    const directory = ServerContext.useStoreState((state) => state.files.directory);

    useEffect(() => {
        clearFlashes('files:rename-modal');
    }, [props.visible]);

    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);

    const submit = ({ name }: FormikValues, { setSubmitting }: FormikHelpers<FormikValues>) => {
        clearFlashes('files');
        clearFlashes('files:rename-modal');

        let data;

        if (useMoveTerminology && files.length > 1) {
            data = files.map((f) => ({ from: f, to: join(name, f) }));
        } else {
            data = files.map((f) => ({ from: f, to: name }));
        }

        return renameFiles(uuid, directory, data)
            .then((): Promise<any> => (files.length > 0 ? mutate() : Promise.resolve()))
            .then(() => {
                setSelectedFiles([]);
                props.onDismissed();
            })
            .catch((error) => {
                mutate();
                setSubmitting(false);
                clearAndAddHttpError({ key: 'files:rename-modal', error });
            });
    };

    return (
        <Formik onSubmit={submit} initialValues={{ name: files.length > 1 ? '' : files[0] || '' }}>
            {({ isSubmitting, values }) => (
                <Modal
                    {...props}
                    accessibleName={t(useMoveTerminology ? 'move' : 'rename')}
                    dismissable={!isSubmitting}
                    showSpinnerOverlay={isSubmitting}
                >
                    <FlashMessageRender byKey='files:rename-modal' className='mb-4' />
                    <Form className='m-0'>
                        <div className={clsx('flex flex-wrap', useMoveTerminology ? 'items-center' : 'items-end')}>
                            <div className='w-full sm:flex-1 sm:mr-4'>
                                <Field
                                    type='string'
                                    id='file_name'
                                    name='name'
                                    label={t('file-name-label')}
                                    description={useMoveTerminology ? t('move-description') : undefined}
                                    autoFocus
                                />
                            </div>
                            <div className='w-full sm:w-auto mt-4 sm:mt-0'>
                                <Button className='w-full'>{useMoveTerminology ? t('move') : t('rename')}</Button>
                            </div>
                        </div>
                        {useMoveTerminology && (
                            <p className='text-xs mt-2 text-gray-400'>
                                <strong className='text-gray-200'>{t('new-location')}</strong>
                                &nbsp;/home/container/{join(directory, values.name).replace(/^(\.\.\/|\/)+/, '')}
                            </p>
                        )}
                    </Form>
                </Modal>
            )}
        </Formik>
    );
};

export default RenameFileModal;
