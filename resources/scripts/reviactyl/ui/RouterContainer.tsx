import React from 'react';
import classNames from 'classnames';

export const RouterContainer = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
    <div
        className={classNames(
            'h-full min-h-screen bg-gray-950 bg-fixed bg-center bg-no-repeat bg-[image:var(--background)] bg-cover',
            className,
        )}
        {...props}
    />
);
