import React, { forwardRef } from 'react';
import { Field as FormikField, FieldProps } from 'formik';
import Input from '@/reviactyl/elements/Input';
import Label from '@/reviactyl/elements/Label';

interface OwnProps {
    name: string;
    icon?: React.ComponentType<{ className?: string }>;
    label?: string;
    description?: string;
    validate?: (value: any) => undefined | string | Promise<any>;
}
type Props = OwnProps & Omit<React.InputHTMLAttributes<HTMLInputElement>, 'name'>;

const Field = forwardRef<HTMLInputElement, Props>(
    ({ id, name, icon: Icon, label, description, validate, ...props }, ref) => (
        <FormikField innerRef={ref} name={name} validate={validate}>
            {({ field, form: { errors, touched } }: FieldProps) => (
                <div>
                    {label && <Label htmlFor={id}>{label}</Label>}
                    <div className='flex items-center'>
                        {Icon && (
                            <div
                                className={`rounded-l-ui border border-r-0 bg-gray-800 p-3 ${
                                    touched[field.name] && errors[field.name]
                                        ? 'border-red-400 text-red-400'
                                        : 'border-gray-600 text-gray-600'
                                }`}
                            >
                                <Icon className='w-5 h-5' />
                            </div>
                        )}
                        <Input
                            className={Icon ? 'rounded-l-none!' : undefined}
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
