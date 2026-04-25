import React, { forwardRef } from 'react';
import classNames from 'classnames';
import tw from 'twin.macro';
import styled from 'styled-components';

interface CardProps {
    className?: string;
    children: React.ReactNode;
}

const CardDefault = styled.div`
    ${tw`rounded-ui bg-gray-700 border border-gray-600`}
`;

const Card = forwardRef<HTMLDivElement, CardProps & React.HTMLAttributes<HTMLDivElement>>(
    ({ className, children, ...props }, ref) => (
        <CardDefault ref={ref} {...props} className={classNames('p-5', className)}>
            {children}
        </CardDefault>
    )
);

Card.displayName = 'Card';

export default Card;
