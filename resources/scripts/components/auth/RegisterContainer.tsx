import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Formik, FormikHelpers } from 'formik';
import { object, string, ref as yupRef } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import useFlash from '@/plugins/useFlash';
import Label from '@/components/elements/Label';
import { KeyIcon, UserIcon, MailIcon, EyeIcon, EyeOffIcon } from '@heroicons/react/solid';
import Reaptcha from 'reaptcha';
import Turnstile from '@/components/elements/Turnstile';
import { useStoreState } from 'easy-peasy';

import register from '@/api/auth/register';
import { useTranslation } from 'react-i18next';

interface Values {
    email: string;
    username: string;
    firstName: string;
    lastName: string;
    password: string;
    passwordConfirmation: string;
}

const RegisterContainer = () => {
    const { t } = useTranslation('auth');
    const ref = React.useRef<Reaptcha>(null);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [show, setShow] = useState(false);
    const [token, setToken] = useState('');

    const { provider, recaptcha, turnstile } = useStoreState((state) => state.settings.data!.captcha);

    useEffect(() => {
        clearFlashes();

        // Keep session alive for registration
        const interval = setInterval(() => {
            fetch('/').catch(() => { /* ignore */ });
        }, 1000 * 60 * 5); // 5 minutes

        return () => clearInterval(interval);
    }, []);

    const onSubmit = (values: Values, { setSubmitting, setErrors }: FormikHelpers<Values>) => {
        clearFlashes();

        // If using reCAPTCHA and no token yet, execute captcha
        if (provider === 'recaptcha' && !token) {
            ref.current?.execute();
            return;
        }

        register({
            email: values.email,
            username: values.username,
            first_name: values.firstName,
            last_name: values.lastName,
            password: values.password,
            password_confirmation: values.passwordConfirmation,
            'g-recaptcha-response': provider === 'recaptcha' ? token : undefined,
            'cf-turnstile-response': provider === 'turnstile' ? token : undefined,
        })
            .then((response) => {
                if (response.complete) {
                    (window as any).location = response.intended || '/';
                    return;
                }
            })
            .catch((error) => {
                setToken('');
                if (ref.current) ref.current.reset();

                const { response } = error;
                if (response?.status === 422 && response?.data?.errors) {
                    const errors: Record<string, string> = {};
                    Object.keys(response.data.errors).forEach((key) => {
                        const message = response.data.errors[key][0];
                        if (key === 'name_first' || key === 'first_name') {
                            errors.firstName = message;
                        } else if (key === 'name_last' || key === 'last_name') {
                            errors.lastName = message;
                        } else if (key === 'password_confirmation') {
                            errors.passwordConfirmation = message;
                        } else {
                            errors[key] = message;
                        }
                    });
                    setErrors(errors);
                } else {
                    clearAndAddHttpError({ error });
                }

                setSubmitting(false);
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ email: '', username: '', firstName: '', lastName: '', password: '', passwordConfirmation: '' }}
            validationSchema={object().shape({
                email: string().email().required(),
                username: string().required().min(3),
                firstName: string().required(),
                lastName: string().required(),
                password: string().required().min(8),
                passwordConfirmation: string().required().oneOf([yupRef('password')], t('register.no-match')),
            })}
        >
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={'Create Account'} css={tw`w-full flex`}>
                    <div css={tw`grid grid-cols-2 gap-4`}>
                        <Field
                            label={t('register.namefirst-label')}
                            placeholder={'John'}
                            name={'firstName'}
                            disabled={isSubmitting}
                        />
                        <Field
                            label={t('register.namelast-label')}
                            placeholder={'Doe'}
                            name={'lastName'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-3`}>
                        <Field
                            icon={MailIcon}
                            type={'email'}
                            label={t('register.email-label')}
                            placeholder={'john.doe@example.com'}
                            name={'email'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-3`}>
                        <Field
                            icon={UserIcon}
                            label={t('register.username-label')}
                            placeholder={'john.doe'}
                            name={'username'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-3 relative`}>
                        <Label>{t('register.password-label')}</Label>
                        <Field
                            icon={KeyIcon}
                            type={show ? 'text' : 'password'}
                            name={'password'}
                            placeholder={'••••••••'}
                            description={t('reset-password.min-length')}
                            disabled={isSubmitting}
                        />
                        <button
                            type={'button'}
                            css={tw`absolute border-l-2 top-[34px] right-[6px] py-2 p-1 border-gray-300 text-gray-300`}
                            onClick={() => setShow(!show)}
                        >
                            {show ? <EyeOffIcon className='h-5 w-5' /> : <EyeIcon className='h-5 w-5' />}
                        </button>
                    </div>
                    <div css={tw`mt-3 relative`}>
                        <Label>{t('register.confirm-label')}</Label>
                        <Field
                            icon={KeyIcon}
                            type={show ? 'text' : 'password'}
                            placeholder={'••••••••'}
                            name={'passwordConfirmation'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-6`}>
                        <Button css={tw`w-full !py-3`} type={'submit'} disabled={isSubmitting}>
                            {t('register.label')}
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
                    <div css={tw`mt-4 text-center`}>
                        <Link
                            to={'/auth/login'}
                            css={tw`text-sm text-reviactyl/80 tracking-wide no-underline hover:text-reviactyl/50`}
                        >
                            Already have an account? Login
                        </Link>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default RegisterContainer;
