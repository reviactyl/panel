import React, { forwardRef } from 'react';
import { Field as FormikField, FieldProps } from 'formik';
import Input from '@/reviactyl/elements/Input';
import Label from '@/reviactyl/elements/Label';
import tw from 'twin.macro';
import styled from 'styled-components';

interface OwnProps {
    name: string;
    icon?: React.ComponentType<{ className?: string }>;
    label?: string;
    description?: string;
    validate?: (value: any) => undefined | string | Promise<any>;
}
const IconWrapper = styled.div`
    ${tw`bg-gray-700 border-2 !border-r-0 rounded-l-ui p-3`}
`;

type Props = OwnProps & Omit<React.InputHTMLAttributes<HTMLInputElement>, 'name'>;

const Field = forwardRef<HTMLInputElement, Props>(
    ({ id, name, icon: Icon, label, description, validate, ...props }, ref) => (
        <FormikField innerRef={ref} name={name} validate={validate}>
            {({ field, form: { errors, touched } }: FieldProps) => (
                <div>
                    {label && <Label htmlFor={id}>{label}</Label>}
                    <div className='flex items-center'>
                        {Icon && (
                            <IconWrapper
                                className={
                                    touched[field.name] && errors[field.name]
                                        ? 'border-red-400 text-red-400'
                                        : 'border-gray-600 text-gray-600'
                                }
                            >
                                <Icon className='w-5 h-5' />
                            </IconWrapper>
                        )}
                        <Input
                            css={[Icon && tw`!rounded-l-none`]}
                            id={id}
                            {...field}
                            {...props}
                            $hasError={!!(touched[field.name] && errors[field.name])}
                        />
                    </div>
                    <div>
                        {touched[field.name] && errors[field.name] ? (
                            <p className={'mt-1 text-xs text-red-200'}>
                                {(errors[field.name] as string).charAt(0).toUpperCase() +
                                    (errors[field.name] as string).slice(1)}
                            </p>
                        ) : description ? (
                            <p className={'mt-1 text-xs text-gray-200'}>{description}</p>
                        ) : null}
                    </div>
                </div>
            )}
        </FormikField>
    )
);
Field.displayName = 'Field';

export default Field;
