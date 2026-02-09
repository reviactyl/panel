import React, { useEffect, useRef, useState } from 'react';
import { Link, RouteComponentProps } from 'react-router-dom';
import login from '@/api/auth/login';
import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { useStoreState } from 'easy-peasy';
import { Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Field from '@/components/elements/Field';
import tw from 'twin.macro';
import { Button } from '@/components/elements/button/index';
import Reaptcha from 'reaptcha';
import Turnstile from '@/components/elements/Turnstile';
import useFlash from '@/plugins/useFlash';
import Label from '@/components/elements/Label';
import { KeyIcon, UserIcon, EyeIcon, EyeOffIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

interface Values {
    username: string;
    password: string;
}

const LoginContainer = ({ history }: RouteComponentProps) => {
    const { t } = useTranslation('auth');
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');
    const [show, setShow] = useState(false);

    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const { provider, recaptcha, turnstile } = useStoreState((state) => state.settings.data!.captcha);

    const socialSettings = window.SocialLoginConfiguration || { google: false, discord: false, github: false };

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

        // For Turnstile, the token is set automatically by the widget
        if (provider === 'turnstile' && !token) {
            setSubmitting(false);
            return;
        }

        login({ ...values, captchaToken: token, captchaProvider: provider })
            .then((response) => {
                if (response.complete) {
                    // @ts-expect-error this is valid
                    window.location = response.intended || '/';
                    return;
                }

                history.replace('/auth/login/checkpoint', { token: response.confirmationToken });
            })
            .catch((error) => {
                console.error(error);

                setToken('');
                if (ref.current) ref.current.reset();

                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
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
            {({ isSubmitting, setSubmitting, submitForm }) => (
                <LoginFormContainer title={t('login-title')} css={tw`w-full flex`}>
                    <Field
                        icon={UserIcon}
                        type={'text'}
                        placeholder={t('username-label')}
                        label={t('username-label')}
                        name={'username'}
                        disabled={isSubmitting}
                    />
                    <div css={tw`mt-3`}>
                        <Label>{t('password-label')}</Label>
                        <div css={tw`relative`}>
                            <Field
                                icon={KeyIcon}
                                type={show ? 'text' : 'password'}
                                placeholder={t('password-label')}
                                name={'password'}
                                disabled={isSubmitting}
                            />
                            <button
                                type={'button'}
                                css={tw`absolute border-l-2 top-[10px] right-[6px] py-2 p-1 border-gray-300 text-gray-300`}
                                onClick={() => setShow(!show)}
                            >
                                {show ? <EyeOffIcon className='h-5 w-5' /> : <EyeIcon className='h-5 w-5' />}
                            </button>
                        </div>
                    </div>
                    <div css={tw`mt-6`}>
                        <Button css={tw`w-full !py-3`} type={'submit'} disabled={isSubmitting}>
                            {t('login-button')}
                        </Button>
                    </div>

                    <div css={tw`mt-4 grid grid-cols-1 gap-2`}>
                        <div css={tw`relative flex py-2 items-center`}>
                            <div css={tw`flex-grow border-t border-gray-600`}></div>
                            <span css={tw`flex-shrink mx-4 text-gray-400 text-xs`}>{t('social.or')}</span>
                            <div css={tw`flex-grow border-t border-gray-600`}></div>
                        </div>
                        {socialSettings.google && (
                            <Button
                                onClick={() => (window.location.href = '/auth/login/google')}
                                css={tw`w-full !py-3 !bg-green-600`}
                            >
                                {t('social.google')}
                            </Button>
                        )}
                        {socialSettings.discord && (
                            <Button
                                onClick={() => (window.location.href = '/auth/login/discord')}
                                css={tw`w-full !py-3 !bg-indigo-600`}
                            >
                                {t('social.discord')}
                            </Button>
                        )}
                        {socialSettings.github && (
                            <Button
                                onClick={() => (window.location.href = '/auth/login/github')}
                                css={tw` !py-3 !bg-white !text-black`}
                            >
                                {t('social.github')}
                            </Button>
                        )}
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
                    <div css={tw`mt-3 flex flex-col items-center gap-2`}>
                        <Link
                            to={'/auth/password'}
                            css={tw`text-sm text-reviactyl/80 tracking-wide no-underline hover:text-reviactyl/50`}
                        >
                            {t('forgot-password.label')}
                        </Link>
                        <Link
                            to={'/auth/register'}
                            css={tw`text-xs text-gray-400 tracking-wide no-underline hover:text-gray-300`}
                        >
                            Don&apos;t have an account? Create one
                        </Link>
                        {window.ReviactylConfiguration?.billingCardLink && (
                            <a
                                href={window.ReviactylConfiguration.billingCardLink}
                                target={'_blank'}
                                rel={'noreferrer'}
                                css={tw`mt-2 flex items-center gap-1 text-xs text-indigo-400 hover:text-indigo-300 no-underline transition-colors duration-200`}
                            >
                                <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 fill-current">
                                    <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152c-.0766.1369-.1707.33-.2346.4883-1.8439-.2753-3.6702-.2753-5.4646 0-.0639-.1583-.158-.3514-.2346-.4883a19.7358 19.7358 0 00-4.8851 1.5152c-3.1312 4.6735-3.9827 9.223-3.5658 13.7048.0135.1451.1098.2735.2346.368a20.0152 20.0152 0 002.9431 1.488c.1197.0427.2519.0063.3298-.0887.2796-.3815.525-.7832.7352-1.2057.0396-.0809.0018-.1767-.0809-.208a12.7239 12.7239 0 01-1.8653-.889c-.0868-.0461-.0916-.1689-.0093-.2216a9.9826 9.9826 0 00.4132-.3193c.0463-.037.1037-.0388.1517-.0157 3.8247 1.7456 7.9547 1.7456 11.7225 0 .048-.0231.1054-.0213.1517.0157.1352.1077.2739.2144.4132.3193.0823.0527.0775.1755-.0093.2216a12.724 12.724 0 01-1.8653.889c-.0827.0313-.1205.1271-.0809.208.2102.4225.4556.8242.7352 1.2057.0779.095.2101.1314.3298.0887a20.0075 20.0075 0 002.9431-1.488c.1248-.0945.2211-.2229.2346-.368.4907-5.2662-.8651-9.7538-3.5658-13.7048zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.095 2.1568 2.419 0 1.3332-.9655 2.4189-2.1569 2.4189zm7.9744 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.095 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189z" />
                                </svg>
                                Discord Billing Support
                            </a>
                        )}
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
};

export default LoginContainer;
