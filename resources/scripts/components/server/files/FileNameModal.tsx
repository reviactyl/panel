import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/reviactyl/elements/Field';
import { ServerContext } from '@/state/server';
import { join } from 'pathe';
import Button from '@/reviactyl/elements/Button';
import { useTranslation } from 'react-i18next';
import { httpErrorToHuman } from '@/api/http';

type Props = RequiredModalProps & {
    onFileNamed: (name: string) => Promise<void>;
};

interface Values {
    fileName: string;
}

export default ({ onFileNamed, onDismissed, ...props }: Props) => {
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { t } = useTranslation(['strings', 'validation']);

    const submit = async (values: Values, { setFieldError, setSubmitting }: FormikHelpers<Values>) => {
        try {
            await onFileNamed(join(directory, values.fileName));
        } catch (error) {
            setFieldError('fileName', httpErrorToHuman(error));
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Formik
            onSubmit={submit}
            initialValues={{ fileName: '' }}
            validationSchema={object().shape({
                fileName: string().required().min(1),
            })}
        >
            {({ resetForm, isSubmitting }) => (
                <Modal
                    onDismissed={() => {
                        resetForm();
                        onDismissed();
                    }}
                    {...props}
                    accessibleName={t('create-file')}
                    dismissable={!isSubmitting}
                >
                    <Form>
                        <Field
                            id={'fileName'}
                            name={'fileName'}
                            label={t('file-name')}
                            description={t('file-name-description')}
                            autoFocus
                            disabled={isSubmitting}
                        />
                        <div className='mt-6 text-right'>
                            <Button disabled={isSubmitting}>{t('create-file')}</Button>
                        </div>
                    </Form>
                </Modal>
            )}
        </Formik>
    );
};
