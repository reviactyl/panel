import React, { Children, cloneElement, isValidElement, useId } from 'react';
import { Field, FieldProps } from 'formik';
import InputError from '@/reviactyl/elements/InputError';
import Label from '@/reviactyl/elements/Label';

interface Props {
    id?: string;
    name: string;
    children: React.ReactNode | ((props: { id: string }) => React.ReactNode);
    className?: string;
    label?: string;
    description?: string;
    validate?: (value: any) => undefined | string | Promise<any>;
}

const FormikFieldWrapper = ({ id, name, label, className, description, validate, children }: Props) => {
    const generatedId = useId();
    const control =
        typeof children === 'function'
            ? undefined
            : Children.toArray(children).find(
                  (child) => isValidElement<{ name?: string; id?: string }>(child) && child.props.name === name,
              );
    const inputId = id ?? (isValidElement<{ id?: string }>(control) ? control.props.id : undefined) ?? generatedId;

    return (
        <Field name={name} validate={validate}>
            {({ field, form: { errors, touched } }: FieldProps) => (
                <div className={`${className} ${touched[field.name] && errors[field.name] ? 'has-error' : undefined}`}>
                    {label && <Label htmlFor={inputId}>{label}</Label>}
                    {typeof children === 'function'
                        ? children({ id: inputId })
                        : Children.map(children, (child) =>
                              isValidElement<{ name?: string; id?: string }>(child) && child.props.name === name
                                  ? cloneElement(child, { id: inputId })
                                  : child,
                          )}
                    <InputError errors={errors} touched={touched} name={field.name}>
                        {description || null}
                    </InputError>
                </div>
            )}
        </Field>
    );
};

export default FormikFieldWrapper;
