import { Field, Form, Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import FormikFieldWrapper from '@/reviactyl/elements/FormikFieldWrapper';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import Button from '@/reviactyl/elements/Button';
import Input, { Textarea } from '@/reviactyl/elements/Input';
import { useFlashKey } from '@/plugins/useFlash';
import { createSSHKey, useSSHKeys } from '@/api/account/ssh-keys';
import { useTranslation } from 'react-i18next';
import { httpErrorToHuman } from '@/api/http';

interface Values {
    name: string;
    publicKey: string;
}

export default () => {
    const { t } = useTranslation('dashboard/account');
    const { clearFlashes, addError } = useFlashKey('account');
    const { data, mutate } = useSSHKeys();

    const submit = (values: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearFlashes();

        createSSHKey(values.name, values.publicKey)
            .then((key) => {
                resetForm();
                if (data) {
                    void mutate((keys) => keys?.concat(key), false);
                } else {
                    void mutate().catch(() => undefined);
                }
            })
            .catch((error) => addError(httpErrorToHuman(error)))
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
                            <Button type={'submit'} disabled={isSubmitting}>
                                {t('ssh.create.save')}
                            </Button>
                        </div>
                    </Form>
                )}
            </Formik>
        </>
    );
};
