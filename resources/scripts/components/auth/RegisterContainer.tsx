import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Formik, FormikErrors, FormikHelpers } from 'formik';
import { object, string, ref as yupRef } from 'yup';
import Field from '@/reviactyl/elements/Field';
import { Button } from '@/reviactyl/components/button/index';
import useFlash from '@/plugins/useFlash';
import Label from '@/reviactyl/elements/Label';
import { KeyIcon, UserIcon, MailIcon, EyeIcon, EyeOffIcon } from '@heroicons/react/solid';
import Reaptcha from 'reaptcha';
import Turnstile, { type TurnstileHandle } from '@/reviactyl/elements/Turnstile';
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

const validationFields = new Map<string, keyof Values>([
    ['email', 'email'],
    ['username', 'username'],
    ['first_name', 'firstName'],
    ['name_first', 'firstName'],
    ['last_name', 'lastName'],
    ['name_last', 'lastName'],
    ['password', 'password'],
    ['password_confirmation', 'passwordConfirmation'],
]);

const RegisterContainer = () => {
    const { t } = useTranslation('auth');
    const ref = React.useRef<Reaptcha>(null);
    const pendingCaptcha = React.useRef<((token: string) => void) | null>(null);
    const turnstileRef = React.useRef<TurnstileHandle>(null);
    const { clearFlashes, clearAndAddHttpError, addError } = useFlash();
    const [show, setShow] = useState(false);
    const [token, setToken] = useState('');

    const { provider, recaptcha, turnstile } = useStoreState((state) => state.settings.data!.captcha);

    useEffect(() => {
        clearFlashes();

        // Keep session alive for registration
        const interval = setInterval(
            () => {
                fetch('/').catch(() => {
                    /* ignore */
                });
            },
            1000 * 60 * 5,
        ); // 5 minutes

        return () => clearInterval(interval);
    }, []);

    const failCaptcha = (setSubmitting: (submitting: boolean) => void) => {
        if (!pendingCaptcha.current) return;
        pendingCaptcha.current = null;
        setToken('');
        setSubmitting(false);
        addError({ message: t('captcha-failed') });
        ref.current?.reset().catch(() => {});
    };

    const performRegistration = (
        values: Values,
        { setSubmitting, setErrors }: FormikHelpers<Values>,
        captchaToken: string,
    ) => {
        register({
            email: values.email,
            username: values.username,
            first_name: values.firstName,
            last_name: values.lastName,
            password: values.password,
            password_confirmation: values.passwordConfirmation,
            'g-recaptcha-response': provider === 'recaptcha' ? captchaToken : undefined,
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
                ref.current?.reset().catch(() => {});
                turnstileRef.current?.reset();

                const { response } = error;
                if (response?.status === 422 && Array.isArray(response?.data?.errors)) {
                    const errors: FormikErrors<Values> = {};
                    const unmapped: string[] = [];
                    for (const validationError of response.data.errors) {
                        const field = validationFields.get(validationError?.meta?.source_field);
                        const message = validationError?.detail;
                        if (typeof message !== 'string' || !message.trim()) continue;

                        if (field) {
                            errors[field] ??= message;
                        } else {
                            unmapped.push(message);
                        }
                    }
                    setErrors(errors);
                    if (unmapped.length) {
                        addError({ message: unmapped.join(' ') });
                    } else if (!Object.keys(errors).length) {
                        clearAndAddHttpError({ error });
                    }
                } else {
                    clearAndAddHttpError({ error });
                }

                setSubmitting(false);
            });
    };

    const onSubmit = (values: Values, helpers: FormikHelpers<Values>) => {
        clearFlashes();

        if (provider === 'recaptcha') {
            pendingCaptcha.current = (response) => performRegistration(values, helpers, response);
            Promise.resolve()
                .then(() => {
                    if (!ref.current) throw new Error('reCAPTCHA is unavailable.');
                    return ref.current.execute();
                })
                .catch(() => failCaptcha(helpers.setSubmitting));
            return;
        }

        performRegistration(values, helpers, token);
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{
                email: '',
                username: '',
                firstName: '',
                lastName: '',
                password: '',
                passwordConfirmation: '',
            }}
            validationSchema={object().shape({
                email: string().email().required(),
                username: string().required().min(3),
                firstName: string().required(),
                lastName: string().required(),
                password: string().required().min(8),
                passwordConfirmation: string()
                    .required()
                    .oneOf([yupRef('password')], t('register.no-match')),
            })}
        >
            {({ isSubmitting, setSubmitting }) => (
                <LoginFormContainer title={'Create Account'} className='flex w-full'>
                    <div className='grid grid-cols-2 gap-4'>
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
                    <div className='mt-3'>
                        <Field
                            icon={MailIcon}
                            type={'email'}
                            label={t('register.email-label')}
                            placeholder={'john.doe@example.com'}
                            name={'email'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div className='mt-3'>
                        <Field
                            icon={UserIcon}
                            label={t('register.username-label')}
                            placeholder={'john.doe'}
                            name={'username'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div className='relative mt-3'>
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
                            className='absolute top-9 right-1.5 p-1 py-2 text-gray-500'
                            onClick={() => setShow(!show)}
                        >
                            {show ? <EyeOffIcon className='h-5 w-5' /> : <EyeIcon className='h-5 w-5' />}
                        </button>
                    </div>
                    <div className='relative mt-3'>
                        <Label>{t('register.confirm-label')}</Label>
                        <Field
                            icon={KeyIcon}
                            type={show ? 'text' : 'password'}
                            placeholder={'••••••••'}
                            name={'passwordConfirmation'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div className='mt-6'>
                        <Button className='w-full py-3!' type={'submit'} disabled={isSubmitting}>
                            {t('register.label')}
                        </Button>
                    </div>
                    {provider === 'recaptcha' && (
                        <Reaptcha
                            ref={ref}
                            size={'invisible'}
                            sitekey={recaptcha.siteKey || '_invalid_key'}
                            onVerify={(response) => {
                                const submit = pendingCaptcha.current;
                                if (!submit) return;
                                if (!response?.trim()) {
                                    failCaptcha(setSubmitting);
                                    return;
                                }
                                pendingCaptcha.current = null;
                                submit(response);
                            }}
                            onError={() => failCaptcha(setSubmitting)}
                            onExpire={() => failCaptcha(setSubmitting)}
                        />
                    )}
                    {provider === 'turnstile' && (
                        <div className='mt-4 flex justify-center'>
                            <Turnstile
                                ref={turnstileRef}
                                siteKey={turnstile.siteKey}
                                onVerify={(response) => setToken(response)}
                                onExpire={() => setToken('')}
                            />
                        </div>
                    )}
                    <div className='mt-4 text-center'>
                        <Link
                            to={'/auth/login'}
                            className='text-sm tracking-wide text-reviactyl/80 no-underline hover:text-reviactyl/50'
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
