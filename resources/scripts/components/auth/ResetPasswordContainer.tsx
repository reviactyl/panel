import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import performPasswordReset from '@/api/auth/performPasswordReset';
import { httpErrorToHuman } from '@/api/http';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { Formik, FormikHelpers } from 'formik';
import { object, ref, string } from 'yup';
import Field from '@/reviactyl/elements/Field';
import Input from '@/reviactyl/elements/Input';
import tw from 'twin.macro';
import Button from '@/reviactyl/elements/Button';
import { useTranslation } from 'react-i18next';

interface Values {
    password: string;
    passwordConfirmation: string;
}

function ResetPasswordContainer() {
    const { t } = useTranslation('auth');
    const [email, setEmail] = useState('');

    const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const parsed = new URLSearchParams(location.search);
    if (email.length === 0 && parsed.get('email')) {
        setEmail(parsed.get('email') || '');
    }

    const params = useParams<'token'>();

    const submit = ({ password, passwordConfirmation }: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();
        performPasswordReset(email, { token: params.token ?? '', password, passwordConfirmation })
            .then(() => {
                window.location.href = '/';
            })
            .catch((error) => {
                console.error(error);

                setSubmitting(false);
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            });
    };

    return (
        <Formik
            onSubmit={submit}
            initialValues={{
                password: '',
                passwordConfirmation: '',
            }}
            validationSchema={object().shape({
                password: string().required(t('reset-password.new-required')).min(8, t('reset-password.min-required')),
                passwordConfirmation: string()
                    .required(t('reset-password.no-match'))
                    // @ts-expect-error this is valid
                    .oneOf([ref('password'), null], t('reset-password.no-match')),
            })}
        >
            {({ isSubmitting }) => (
                <LoginFormContainer title={t('reset-password.label')} css={tw`w-full flex`}>
                    <div>
                        <label>{t('reset-password.email-label')}</label>
                        <Input value={email} disabled />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            label={t('reset-password.new-label')}
                            name={'password'}
                            type={'password'}
                            description={t('reset-password.min-length')}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Field
                            label={t('reset-password.confirm-label')}
                            name={'passwordConfirmation'}
                            type={'password'}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button size={'xlarge'} type={'submit'} disabled={isSubmitting} isLoading={isSubmitting}>
                            {t('reset-password.label')}
                        </Button>
                    </div>
                    <div css={tw`mt-3 text-center`}>
                        <Link
                            to={'/auth/login'}
                            css={tw`text-sm text-reviactyl/80 tracking-wide no-underline hover:text-reviactyl/50`}
                        >
                            {t('return')}
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
}

export default ResetPasswordContainer;
