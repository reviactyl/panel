import React from 'react';
import classNames from 'classnames';

interface TitleProps extends React.HTMLAttributes<HTMLDivElement> {
    scheme?: 'gray' | 'primary';
}

const gradientClasses: Record<NonNullable<TitleProps['scheme']>, string> = {
    primary: 'from-reviactyl/60 via-reviactyl/80 to-reviactyl/90',
    gray: 'from-gray-50 via-gray-100 to-gray-200',
};

export const Title = ({ className, children, scheme = 'gray', ...props }: TitleProps) => {
    const colorClass = gradientClasses[scheme];

    return (
        <div
            className={classNames(
                'leading-tight bg-gradient-to-tl bg-clip-text font-semibold text-transparent',
                colorClass,
                className
            )}
            {...props}
        >
            {children}
        </div>
    );
};

export default Title;
