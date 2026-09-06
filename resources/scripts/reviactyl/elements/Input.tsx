import React, { forwardRef } from 'react';
import classNames from 'classnames';
import styles from '@/reviactyl/elements/inputs/styles.module.css';

export interface Props {
    $hasError?: boolean;
}
type InputProps = Props & React.InputHTMLAttributes<HTMLInputElement>;
const Input = forwardRef<HTMLInputElement, InputProps>(({ $hasError, className, type, ...props }, ref) => (
    <input
        ref={ref}
        type={type}
        data-has-error={!!$hasError}
        className={classNames(
            type === 'checkbox' || type === 'radio' ? styles.checkbox : styles.input,
            type === 'radio' && styles.radio,
            className
        )}
        {...props}
    />
));
type TextareaProps = Props & React.TextareaHTMLAttributes<HTMLTextAreaElement>;
const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(({ $hasError, className, ...props }, ref) => (
    <textarea ref={ref} data-has-error={!!$hasError} className={classNames(styles.input, className)} {...props} />
));
Input.displayName = 'Input';
Textarea.displayName = 'Textarea';
export { Textarea };
export default Input;
