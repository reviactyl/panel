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

import register from '@/api/auth/register';

interface Values {
    email: string;
    username: string;
    firstName: string;
    lastName: string;
    password: string;
    passwordConfirmation: string;
}

const RegisterContainer = () => {
    const { clearFlashes, addFlash, clearAndAddHttpError } = useFlash();
    const [show, setShow] = useState(false);

    useEffect(() => {
        clearFlashes();

        // Keep session alive for registration
        const interval = setInterval(() => {
            fetch('/').catch(() => { });
        }, 1000 * 60 * 5); // 5 minutes

        return () => clearInterval(interval);
    }, []);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();
        register({
            email: values.email,
            username: values.username,
            first_name: values.firstName,
            last_name: values.lastName,
            password: values.password,
            password_confirmation: values.passwordConfirmation,
        })
            .then(() => {
                addFlash({
                    type: 'success',
                    title: 'Registration',
                    message: 'Account created successfully! You can now login.',
                });
                window.location.href = '/auth/login';
            })
            .catch((error) => {
                clearAndAddHttpError({ error });
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
                passwordConfirmation: string().required().oneOf([yupRef('password')], 'Passwords must match'),
            })}
        >
            {({ isSubmitting }) => (
                <LoginFormContainer title={'Create Account'} css={tw`w-full flex`}>
                    <div css={tw`grid grid-cols-2 gap-4`}>
                        <Field
                            label={'First Name'}
                            name={'firstName'}
                            disabled={isSubmitting}
                        />
                        <Field
                            label={'Last Name'}
                            name={'lastName'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-3`}>
                        <Field
                            icon={MailIcon}
                            type={'email'}
                            label={'Email Address'}
                            name={'email'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-3`}>
                        <Field
                            icon={UserIcon}
                            label={'Username'}
                            name={'username'}
                            disabled={isSubmitting}
                        />
                    </div>
                    <div css={tw`mt-3 relative`}>
                        <Label>Password</Label>
                        <Field
                            icon={KeyIcon}
                            type={show ? 'text' : 'password'}
                            name={'password'}
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
                    <div css={tw`mt-6`}>
                        <Button css={tw`w-full !py-3`} type={'submit'} disabled={isSubmitting}>
                            Register
                        </Button>
                    </div>
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
