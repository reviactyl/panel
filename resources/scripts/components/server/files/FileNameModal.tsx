import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/reviactyl/elements/Field';
import { ServerContext } from '@/state/server';
import { join } from 'pathe';
import tw from 'twin.macro';
import Button from '@/reviactyl/elements/Button';
import { useTranslation } from 'react-i18next';

type Props = RequiredModalProps & {
    onFileNamed: (name: string) => void;
};

interface Values {
    fileName: string;
}

export default ({ onFileNamed, onDismissed, ...props }: Props) => {
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { t } = useTranslation('strings');

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        onFileNamed(join(directory, values.fileName));
        setSubmitting(false);
    };

    return (
        <Formik
            onSubmit={submit}
            initialValues={{ fileName: '' }}
            validationSchema={object().shape({
                fileName: string().required().min(1),
            })}
        >
            {({ resetForm }) => (
                <Modal
                    onDismissed={() => {
                        resetForm();
                        onDismissed();
                    }}
                    {...props}
                >
                    <Form>
                        <Field
                            id={'fileName'}
                            name={'fileName'}
                            label={t('file-name')}
                            description={t('file-name-description')}
                            autoFocus
                        />
                        <div css={tw`mt-6 text-right`}>
                            <Button>{t('create-file')}</Button>
                        </div>
                    </Form>
                </Modal>
            )}
        </Formik>
    );
};
