import React, { forwardRef } from 'react';
import classNames from 'classnames';

interface CardProps {
    className?: string;
    children: React.ReactNode;
}

const Card = forwardRef<HTMLDivElement, CardProps & React.HTMLAttributes<HTMLDivElement>>(
    ({ className, children, ...props }, ref) => (
        <div
            ref={ref}
            {...props}
            className={classNames('rounded-ui border border-gray-800 bg-gray-900 p-5', className)}
        >
            {children}
        </div>
    ),
);

Card.displayName = 'Card';

export default Card;
