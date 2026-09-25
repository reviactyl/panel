import { useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import login from '@/api/auth/login';
import loginWithPasskey from '@/api/auth/loginWithPasskey';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import type { FormikHelpers } from 'formik';
import { Formik } from 'formik';
import { object, string } from 'yup';
import Field from '@/reviactyl/elements/Field';
import { Button } from '@/reviactyl/components/button/index';
import Reaptcha from 'reaptcha';
import Turnstile from '@/reviactyl/elements/Turnstile';
import useFlash from '@/plugins/useFlash';
import Label from '@/reviactyl/elements/Label';
import Spinner from '@/reviactyl/elements/Spinner';
import { KeyIcon, UserIcon, EyeIcon, EyeOffIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';
import OAuthButtons from '@/components/auth/OAuthButtons';

interface Values {
    username: string;
    password: string;
}

function LoginContainer() {
    const { t } = useTranslation('auth');
    const primaryButtonClass = 'w-full !py-3';
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');
    const [show, setShow] = useState(false);
    const [isPasskeySubmitting, setIsPasskeySubmitting] = useState(false);

    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const { provider, recaptcha, turnstile } = useStoreState((state) => state.settings.data!.captcha);
    const passkeyLoginRequiresUsername = useStoreState(
        (state) => state.settings.data?.passkeys?.loginRequiresUsername ?? false,
    );
    const registrationEnabled = useStoreState((state) => state.settings.data?.registrationEnabled ?? true);

    const socialSettings = window.SocialLoginConfiguration || { google: false, discord: false, github: false };

    const navigate = useNavigate();

    const mapPasskeyError = (error: unknown): Error => {
        if (!(error instanceof Error)) {
            return new Error(t('passkey-failed'));
        }

        if (/timed out|not allowed/i.test(error.message)) {
            return new Error(t('passkey-no-credentials'));
        }

        if (error.message === 'PASSKEY_NO_CREDENTIAL') {
            return new Error(t('passkey-no-credentials'));
        }

        if (error.message === 'PASSKEY_UNSUPPORTED') {
            return new Error(t('passkey-unsupported'));
        }

        if (error.message === 'PASSKEY_SECURITY_ERROR') {
            return new Error(t('passkey-security'));
        }

        if (error.message === 'PASSKEY_LOGIN_FAILED') {
            return new Error(t('passkey-failed'));
        }

        return error;
    };

    useEffect(() => {
        clearFlashes();

        // @ts-expect-error this is valid
        const sessionFlashes = window.SessionFlashes;
        if (sessionFlashes) {
            if (sessionFlashes.error) {
                addFlash({ type: 'error', title: 'Error', message: sessionFlashes.error });
            }
            if (sessionFlashes.success) {
                addFlash({ type: 'success', title: 'Success', message: sessionFlashes.success });
            }
            if (sessionFlashes.info) {
                addFlash({ type: 'info', title: 'Info', message: sessionFlashes.info });
            }
            if (sessionFlashes.warning) {
                addFlash({ type: 'warning', title: 'Warning', message: sessionFlashes.warning });
            }
            // @ts-expect-error this is valid
            window.SessionFlashes = undefined;
        }
    }, []);

    const performLogin = (values: Values, captchaToken: string, setSubmitting: (isSubmitting: boolean) => void) => {
        login({ ...values, captchaToken, captchaProvider: provider })
            .then((response) => {
                if (response.complete) {
                    window.location.href = response.intended || '/';
                    return;
                }

                navigate('/auth/login/checkpoint', { state: { token: response.confirmationToken } });
            })
            .catch((error) => {
                console.error(error);

                setToken('');
                if (ref.current) ref.current.reset();

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
    };

    const performPasskeyLogin = (username: string, setSubmitting: (isSubmitting: boolean) => void) => {
        clearFlashes();

        if (passkeyLoginRequiresUsername && !username.trim()) {
            clearAndAddHttpError({ error: new Error(t('passkey-username-required')) });
            return;
        }

        setIsPasskeySubmitting(true);
        setSubmitting(true);

        loginWithPasskey(username)
            .then((response) => {
                if (response.complete) {
                    window.location.href = response.intended || '/';
                    return;
                }

                navigate('/auth/login/checkpoint', { state: { token: response.confirmationToken } });
            })
            .catch((error) => {
                console.error(error);
                setIsPasskeySubmitting(false);
                setSubmitting(false);
                clearAndAddHttpError({ error: mapPasskeyError(error) });
            });
    };

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        // If using reCAPTCHA and no token yet, execute captcha
        if (provider === 'recaptcha' && !token) {
            ref.current!.execute().catch((error) => {
                console.error(error);
                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
            return;
        }

        // For Turnstile, require captcha completion before allowing submit
        if (provider === 'turnstile' && !token) {
            addFlash({ type: 'error', title: 'Error', message: t('captcha-required') });
            setSubmitting(false);
            return;
        }

        performLogin(values, token, setSubmitting);
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ username: '', password: '' }}
            validationSchema={object().shape({
                username: string().required(t('username-required')),
                password: string().required(t('password-required')),
            })}
        >
            {({ isSubmitting, setSubmitting, values }) => (
                <LoginFormContainer title={t('login-title')} className='flex w-full'>
                    <Field
                        icon={UserIcon}
                        type={'text'}
                        placeholder={t('username-label')}
                        label={t('username-label')}
                        name={'username'}
                        disabled={isSubmitting}
                    />
                    <div className='mt-3'>
                        <Label>{t('password-label')}</Label>
                        <div className='relative'>
                            <Field
                                icon={KeyIcon}
                                type={show ? 'text' : 'password'}
                                placeholder={t('password-label')}
                                name={'password'}
                                disabled={isSubmitting}
                            />
                            <button
                                type={'button'}
                                className='absolute top-[10px] right-[6px] p-1 py-2 text-gray-500'
                                onClick={() => setShow(!show)}
                            >
                                {show ? <EyeIcon className='h-5 w-5' /> : <EyeOffIcon className='h-5 w-5' />}
                            </button>
                        </div>
                    </div>
                    <div className='mt-6'>
                        <Button className={primaryButtonClass} type={'submit'} disabled={isSubmitting}>
                            {t('login-button')}
                        </Button>
                    </div>
                    <div className='mt-3'>
                        <Button.Text
                            className={primaryButtonClass}
                            type={'button'}
                            disabled={isSubmitting}
                            onClick={() => performPasskeyLogin(values.username, setSubmitting)}
                        >
                            <span className='relative flex w-full items-center justify-center'>
                                <span className={isPasskeySubmitting ? 'invisible leading-6' : 'leading-6'}>
                                    {t('passkey-button')}
                                </span>
                                {isPasskeySubmitting && (
                                    <span className='absolute inset-0 flex items-center justify-center'>
                                        <Spinner size={'small'} />
                                    </span>
                                )}
                            </span>
                        </Button.Text>
                    </div>

                    <OAuthButtons
                        google={socialSettings.google}
                        discord={socialSettings.discord}
                        github={socialSettings.github}
                    />
                    {provider === 'recaptcha' && (
                        <Reaptcha
                            ref={ref}
                            size={'invisible'}
                            sitekey={recaptcha.siteKey || '_invalid_key'}
                            onVerify={(response) => {
                                setToken(response);
                                performLogin(values, response, setSubmitting);
                            }}
                            onExpire={() => {
                                setSubmitting(false);
                                setToken('');
                            }}
                        />
                    )}
                    {provider === 'turnstile' && (
                        <div className='mt-4 flex justify-center'>
                            <Turnstile
                                siteKey={turnstile.siteKey}
                                onVerify={(response) => setToken(response)}
                                onExpire={() => setToken('')}
                            />
                        </div>
                    )}
                    <div className='mt-3 flex flex-col items-center gap-2'>
                        <Link
                            to={'/auth/password'}
                            className='text-sm tracking-wide text-reviactyl/80 no-underline hover:text-reviactyl/50'
                        >
                            {t('forgot-password.label')}
                        </Link>
                        {registrationEnabled && (
                            <Link
                                to={'/auth/register'}
                                className='text-xs tracking-wide text-gray-400 no-underline hover:text-gray-300'
                            >
                                {t('register.create-link')}
                            </Link>
                        )}
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
}

export default LoginContainer;
