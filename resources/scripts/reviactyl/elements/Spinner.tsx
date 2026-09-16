import React, { Suspense } from 'react';
import ErrorBoundary from '@/reviactyl/elements/ErrorBoundary';

export type SpinnerSize = 'small' | 'base' | 'large';

interface Props {
    size?: SpinnerSize;
    centered?: boolean;
    isBlue?: boolean;
    className?: string;
    children?: React.ReactNode;
}

const SpinnerElement = ({ size = 'base', isBlue, className }: Pick<Props, 'size' | 'isBlue' | 'className'>) => (
    <div
        className={[
            'animate-spin rounded-full',
            size === 'small'
                ? 'h-4 w-4 border-2'
                : size === 'large'
                  ? 'h-16 w-16 border-[6px]'
                  : 'h-8 w-8 border-[3px]',
            isBlue ? 'border-blue-200 border-t-blue-600' : 'border-gray-700 border-t-gray-300',
            className,
        ]
            .filter(Boolean)
            .join(' ')}
        style={{
            animationTimingFunction: 'cubic-bezier(0.55, 0.25, 0.25, 0.7)',
        }}
    />
);

const SpinnerFunc = ({ centered, className, ...props }: Props) =>
    centered ? (
        <div className={`flex items-center justify-center ${props.size === 'large' ? 'm-20' : 'm-6'}`}>
            <SpinnerElement {...props} className={className} />
        </div>
    ) : (
        <SpinnerElement {...props} className={className} />
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
