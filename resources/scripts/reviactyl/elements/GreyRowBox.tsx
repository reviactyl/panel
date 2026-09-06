import React, { forwardRef } from 'react';
import classNames from 'classnames';

type GreyRowBoxProps = React.HTMLAttributes<HTMLDivElement> & { $hoverable?: boolean };

const GreyRowBox = forwardRef<HTMLDivElement, GreyRowBoxProps>(
    ({ $hoverable = true, className, children, ...props }, ref) => (
        <div
            ref={ref}
            className={classNames(
                'flex items-center overflow-hidden rounded-ui border border-gray-800 bg-gray-900 p-4 text-gray-200 no-underline transition-colors duration-150 [&_.icon]:flex [&_.icon]:w-16 [&_.icon]:items-center [&_.icon]:justify-center [&_.icon]:rounded-full [&_.icon]:bg-gray-600 [&_.icon]:p-3',
                $hoverable && 'hover:border-gray-600',
                className
            )}
            {...props}
        >
            {children}
        </div>
    )
);

GreyRowBox.displayName = 'GreyRowBox';

export default GreyRowBox;
