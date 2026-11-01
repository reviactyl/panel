import React from 'react';
import classNames from 'classnames';

interface TitleProps extends React.HTMLAttributes<HTMLElement> {
    scheme?: 'gray' | 'primary';
    as?: 'div' | 'h1' | 'h2';
}

const gradientClasses: Record<NonNullable<TitleProps['scheme']>, string> = {
    primary: 'from-reviactyl/60 via-reviactyl/80 to-reviactyl/90',
    gray: 'from-gray-50 via-gray-100 to-gray-200',
};

export const Title = ({ className, children, scheme = 'gray', as: Element = 'div', ...props }: TitleProps) => {
    const colorClass = gradientClasses[scheme];

    return (
        <Element
            className={classNames(
                'leading-tight bg-linear-to-tl bg-clip-text font-semibold text-transparent',
                colorClass,
                className,
            )}
            {...props}
        >
            {children}
        </Element>
    );
};

export default Title;
