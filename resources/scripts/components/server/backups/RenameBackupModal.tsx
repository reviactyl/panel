import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/reviactyl/elements/Field';
import { Button } from '@/reviactyl/components/button';
import { ServerBackup } from '@/api/server/types';
import { useTranslation } from 'react-i18next';
import { Dialog } from '@/reviactyl/elements/dialog';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';

interface FormikValues {
    name: string;
}

interface Props {
    visible: boolean;
    onDismissed: () => void;
    backup: ServerBackup;
    onRenamed: (name: string) => Promise<void>;
}

const RenameBackupModal = ({ visible, onDismissed, backup, onRenamed }: Props) => {
    const { t } = useTranslation('server/backups');

    const submit = ({ name }: FormikValues, { setSubmitting }: FormikHelpers<FormikValues>) => {
        return onRenamed(name)
            .then(() => onDismissed())
            .catch(() => setSubmitting(false));
    };

    return (
        <Formik onSubmit={submit} enableReinitialize initialValues={{ name: backup.name }}>
            {({ isSubmitting, resetForm, submitForm, values }) => (
                <Dialog
                    open={visible}
                    onClose={() => {
                        resetForm();
                        onDismissed();
                    }}
                    title={t('rename')}
                    hideCloseIcon={isSubmitting}
                    preventExternalClose={isSubmitting}
                >
                    <SpinnerOverlay visible={isSubmitting} />
                    <Form className='m-0'>
                        <Field
                            type={'text'}
                            id={'backup_name'}
                            name={'name'}
                            label={t('backup-name')}
                            description={t('name-description')}
                            autoFocus
                        />
                    </Form>
                    <Dialog.Footer>
                        <Button.Text
                            className='w-full sm:w-auto'
                            onClick={() => {
                                resetForm();
                                onDismissed();
                            }}
                            disabled={isSubmitting}
                        >
                            {t('cancel')}
                        </Button.Text>
                        <Button
                            className='w-full sm:w-auto'
                            onClick={submitForm}
                            disabled={isSubmitting || !values.name?.trim()}
                        >
                            {t('rename')}
                        </Button>
                    </Dialog.Footer>
                </Dialog>
            )}
        </Formik>
    );
};

export default RenameBackupModal;
