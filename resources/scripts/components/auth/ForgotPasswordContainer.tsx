import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import requestPasswordResetEmail from '@/api/auth/requestPasswordResetEmail';
import { httpErrorToHuman } from '@/api/http';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import Field from '@/reviactyl/elements/Field';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import tw from 'twin.macro';
import { Button } from '@/reviactyl/components/button/index';
import Reaptcha from 'reaptcha';
import Turnstile from '@/reviactyl/elements/Turnstile';
import useFlash from '@/plugins/useFlash';
import { AtSymbolIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

interface Values {
    email: string;
}

export default () => {
    const { t } = useTranslation('auth');
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');

    const { clearFlashes, addFlash } = useFlash();
    const { provider, recaptcha, turnstile } = useStoreState((state) => state.settings.data!.captcha);

    useEffect(() => {
        clearFlashes();
    }, []);

    const handleSubmission = ({ email }: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearFlashes();

        // If using reCAPTCHA and no token yet, execute captcha
        if (provider === 'recaptcha' && !token) {
            ref.current!.execute().catch((error) => {
                console.error(error);
                setSubmitting(false);
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            });
            return;
        }

        // For Turnstile, the token is set automatically by the widget
        if (provider === 'turnstile' && !token) {
            setSubmitting(false);
            return;
        }

        requestPasswordResetEmail(email, token, provider)
            .then((response) => {
                resetForm();
                addFlash({ type: 'success', title: 'Success', message: response });
            })
            .catch((error) => {
                console.error(error);
                addFlash({ type: 'error', title: 'Error', message: httpErrorToHuman(error) });
            })
            .then(() => {
                setToken('');
                if (ref.current) ref.current.reset();

                setSubmitting(false);
            });
    };

    return (
        <Formik
            onSubmit={handleSubmission}
            initialValues={{ email: '' }}
            validationSchema={object().shape({
                email: string().email(t('email-required')).required(t('email-required')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={t('forgot-password.title')} css={tw`w-full flex`}>
                    <Field
                        icon={AtSymbolIcon}
                        label={t('forgot-password.email-label')}
                        description={t('forgot-password.email-content')}
                        name={'email'}
                        type={'email'}
                    />
                    <div css={tw`mt-6`}>
                        <Button css={tw`w-full !py-3`} type={'submit'} disabled={isSubmitting}>
                            {t('forgot-password.send-email')}
                        </Button>
                    </div>
                    {provider === 'recaptcha' && (
                        <Reaptcha
                            ref={ref}
                            size={'invisible'}
                            sitekey={recaptcha.siteKey || '_invalid_key'}
                            onVerify={(response) => {
                                setToken(response);
                                submitForm();
                            }}
                            onExpire={() => {
                                setSubmitting(false);
                                setToken('');
                            }}
                        />
                    )}
                    {provider === 'turnstile' && (
                        <div css={tw`mt-4 flex justify-center`}>
                            <Turnstile
                                siteKey={turnstile.siteKey}
                                onVerify={(response) => setToken(response)}
                                onExpire={() => setToken('')}
                            />
                        </div>
                    )}
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
};
