import React from 'react';
import classNames from 'classnames';
import tw from 'twin.macro';
import styled from 'styled-components/macro';

interface TitleProps extends React.HTMLAttributes<HTMLDivElement> {
    scheme?: 'gray' | 'primary';
}

const Gradient = styled.div`
    ${tw`leading-tight bg-gradient-to-tl bg-clip-text text-transparent font-semibold`}
`;

const gradientClasses: Record<NonNullable<TitleProps['scheme']>, string> = {
    primary: 'from-reviactyl/60 via-reviactyl/80 to-reviactyl/90',
    gray: 'from-gray-50 via-gray-100 to-gray-200',
};

export const Title = ({ className, children, scheme = 'gray', ...props }: TitleProps) => {
    const colorClass = gradientClasses[scheme];

    return (
        <Gradient className={classNames(colorClass, className)} {...props}>
            {children}
        </Gradient>
    );
};

export default Title;
