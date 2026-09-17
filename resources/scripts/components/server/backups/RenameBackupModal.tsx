import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/reviactyl/elements/Field';
import Button from '@/reviactyl/elements/Button';
import { ServerBackup } from '@/api/server/types';
import { useTranslation } from 'react-i18next';

interface FormikValues {
    name: string;
}

interface Props extends RequiredModalProps {
    backup: ServerBackup;
    onRenamed: (name: string) => Promise<void>;
}

const RenameBackupModal = ({ backup, onRenamed, ...props }: Props) => {
    const { t } = useTranslation('server/backups');

    const submit = ({ name }: FormikValues, { setSubmitting }: FormikHelpers<FormikValues>) => {
        onRenamed(name)
            .then(() => props.onDismissed())
            .catch(() => setSubmitting(false));
    };

    return (
        <Formik onSubmit={submit} enableReinitialize initialValues={{ name: backup.name }}>
            {({ isSubmitting, values }) => (
                <Modal {...props} dismissable={!isSubmitting} showSpinnerOverlay={isSubmitting}>
                    <Form className='m-0'>
                        <div className='flex flex-wrap items-end'>
                            <div className='w-full sm:mr-4 sm:flex-1'>
                                <Field
                                    type={'string'}
                                    id={'backup_name'}
                                    name={'name'}
                                    label={t('backup-name')}
                                    description={t('name-description')}
                                    autoFocus
                                />
                            </div>
                            <div className='mt-4 w-full sm:mt-0 sm:w-auto'>
                                <Button className='w-full' disabled={values.name.trim().length < 1}>
                                    Rename
                                </Button>
                            </div>
                        </div>
                    </Form>
                </Modal>
            )}
        </Formik>
    );
};

export default RenameBackupModal;
