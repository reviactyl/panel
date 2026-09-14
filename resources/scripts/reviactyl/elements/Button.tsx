import React from 'react';
import classNames from 'classnames';
import Spinner from '@/reviactyl/elements/Spinner';

interface Props {
    isLoading?: boolean;
    size?: 'xsmall' | 'small' | 'large' | 'xlarge';
    color?: 'green' | 'red' | 'primary' | 'grey';
    isSecondary?: boolean;
}

const buttonClasses = ({ color, isSecondary, size }: Omit<Props, 'isLoading'>) =>
    classNames(
        'relative inline-block rounded-ui border p-2 text-base font-semibold tracking-wide transition-all duration-150 disabled:opacity-[.55] disabled:cursor-default',
        size === 'xsmall' && 'px-2 py-1 text-xs',
        (!size || size === 'small') && 'px-4 py-2',
        size === 'large' && 'p-4 text-xl',
        size === 'xlarge' && 'w-full p-4',
        isSecondary
            ? 'border-gray-800 bg-transparent text-gray-200 hover:not-disabled:border-gray-600 hover:not-disabled:text-gray-100'
            : color === 'grey'
              ? 'border-gray-800 bg-gray-600 text-gray-50 hover:not-disabled:bg-gray-700'
              : color === 'green'
                ? 'border-green-600 bg-green-500 text-green-50 hover:not-disabled:border-green-700 hover:not-disabled:bg-green-600'
                : color === 'red'
                  ? 'border-red-600 bg-red-500 text-red-50 hover:not-disabled:border-red-700 hover:not-disabled:bg-red-600'
                  : 'border-primary-600/80 bg-primary-500/80 text-primary-50 hover:not-disabled:border-primary-700/80 hover:not-disabled:bg-primary-600/80',
        isSecondary &&
            color === 'red' &&
            'hover:not-disabled:border-red-600 hover:not-disabled:bg-red-500 hover:not-disabled:text-red-50',
        isSecondary &&
            color === 'primary' &&
            'hover:not-disabled:border-primary-600 hover:not-disabled:bg-primary-500 hover:not-disabled:text-primary-50',
        isSecondary &&
            color === 'green' &&
            'hover:not-disabled:border-green-600 hover:not-disabled:bg-green-500 hover:not-disabled:text-green-50',
    );

const ButtonStyle = ({ className, ...props }: ComponentProps) => (
    <button className={classNames(buttonClasses(props), className)} {...props} />
);

type ComponentProps = Omit<React.JSX.IntrinsicElements['button'], 'ref' | keyof Props> & Props;

const Button = ({ children, isLoading, ...props }: ComponentProps) => (
    <ButtonStyle {...props}>
        {isLoading && (
            <div className='absolute top-0 left-0 flex h-full w-full items-center justify-center'>
                <Spinner size={'small'} />
            </div>
        )}
        <span className={isLoading ? 'text-transparent' : undefined}>{children}</span>
    </ButtonStyle>
);

type LinkProps = Omit<React.JSX.IntrinsicElements['a'], 'ref' | keyof Props> & Props;

const LinkButton = ({ className, ...props }: LinkProps) => (
    <a className={classNames(buttonClasses(props), className)} {...props} />
);

export { LinkButton, ButtonStyle };
export default Button;
