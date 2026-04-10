import Modal, { RequiredModalProps } from '@/components/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
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
                    <Form css={tw`m-0`}>
                        <div css={tw`flex flex-wrap items-end`}>
                            <div css={tw`w-full sm:flex-1 sm:mr-4`}>
                                <Field
                                    type={'string'}
                                    id={'backup_name'}
                                    name={'name'}
                                    label={t('backup-name')}
                                    description={t('name-description')}
                                    autoFocus
                                />
                            </div>
                            <div css={tw`w-full sm:w-auto mt-4 sm:mt-0`}>
                                <Button css={tw`w-full`} disabled={values.name.trim().length < 1}>
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
