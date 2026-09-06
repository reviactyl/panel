import { Field, Form, Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import FormikFieldWrapper from '@/reviactyl/elements/FormikFieldWrapper';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import Button from '@/reviactyl/elements/Button';
import Input, { Textarea } from '@/reviactyl/elements/Input';
import { useFlashKey } from '@/plugins/useFlash';
import { createSSHKey, useSSHKeys } from '@/api/account/ssh-keys';
import { useTranslation } from 'react-i18next';

interface Values {
    name: string;
    publicKey: string;
}

export default () => {
    const { t } = useTranslation('dashboard/account');
    const { clearAndAddHttpError } = useFlashKey('account');
    const { mutate } = useSSHKeys();

    const submit = (values: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearAndAddHttpError();

        createSSHKey(values.name, values.publicKey)
            .then((key) => {
                resetForm();
                mutate((data) => (data || []).concat(key));
            })
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setSubmitting(false));
    };

    return (
        <>
            <Formik
                onSubmit={submit}
                initialValues={{ name: '', publicKey: '' }}
                validationSchema={object().shape({
                    name: string().required(),
                    publicKey: string().required(),
                })}
            >
                {({ isSubmitting }) => (
                    <Form>
                        <SpinnerOverlay visible={isSubmitting} />
                        <FormikFieldWrapper label={t('ssh.create.key-name')} name={'name'} className={'mb-6'}>
                            <Field name={'name'} as={Input} />
                        </FormikFieldWrapper>
                        <FormikFieldWrapper
                            label={t('ssh.create.public-key')}
                            name={'publicKey'}
                            description={t('ssh.create.public-key-content')}
                        >
                            <Field className={'h-32'} name={'publicKey'} as={Textarea} />
                        </FormikFieldWrapper>
                        <div className={`flex justify-end mt-6`}>
                            <Button>{t('ssh.create.save')}</Button>
                        </div>
                    </Form>
                )}
            </Formik>
        </>
    );
};
