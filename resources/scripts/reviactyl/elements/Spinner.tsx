import React, { Suspense } from 'react';
import styled, { css, keyframes } from 'styled-components';
import tw from 'twin.macro';
import ErrorBoundary from '@/reviactyl/elements/ErrorBoundary';

export type SpinnerSize = 'small' | 'base' | 'large';

interface Props {
    size?: SpinnerSize;
    centered?: boolean;
    isBlue?: boolean;
    children?: React.ReactNode;
}

const spin = keyframes`
    to { transform: rotate(360deg); }
`;

// noinspection CssOverwrittenProperties
const SpinnerElement = styled.div<Props>`
    ${tw`w-8 h-8`};
    border-width: 3px;
    border-radius: 50%;
    animation: ${spin} 1s cubic-bezier(0.55, 0.25, 0.25, 0.7) infinite;

    ${(props) =>
        props.size === 'small'
            ? tw`w-4 h-4 border-2`
            : props.size === 'large'
            ? css`
                  ${tw`w-16 h-16`};
                  border-width: 6px;
              `
            : null};

    border-color: ${(props) => (!props.isBlue ? 'rgba(255, 255, 255, 0.2)' : 'hsla(212, 92%, 43%, 0.2)')};
    border-top-color: ${(props) => (!props.isBlue ? 'rgb(255, 255, 255)' : 'hsl(212, 92%, 43%)')};
`;

const SpinnerFunc = ({ centered, ...props }: Props) =>
    centered ? (
        <div css={[tw`flex justify-center items-center`, props.size === 'large' ? tw`m-20` : tw`m-6`]}>
            <SpinnerElement {...props} />
        </div>
    ) : (
        <SpinnerElement {...props} />
    );

const SuspenseSpinner = ({ children, centered = true, size, ...props }: Props) => (
    <Suspense fallback={<SpinnerFunc centered={centered} size={size || 'large'} {...props} />}>
        <ErrorBoundary>{children}</ErrorBoundary>
    </Suspense>
);
SuspenseSpinner.displayName = 'Spinner.Suspense';

const Spinner = Object.assign(SpinnerFunc, {
    displayName: 'Spinner',
    Size: {
        SMALL: 'small' as const,
        BASE: 'base' as const,
        LARGE: 'large' as const,
    },
    Suspense: SuspenseSpinner,
});

export default Spinner;
