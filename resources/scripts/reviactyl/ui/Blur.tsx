import React from 'react';
import { useStoreState } from 'easy-peasy';

interface BlurProps extends React.HTMLAttributes<HTMLSpanElement> {
    className?: string;
    children: React.ReactNode;
}

export default function Blur({ className = '', children, ...rest }: BlurProps) {
    const allocationBlur = useStoreState((state) => state.designify.data!.allocationBlur);

    return (
        <span
            {...rest}
            className={`${allocationBlur ? 'duration-300 blur-sm hover:blur-none' : 'blur-none'} ${className}`}
        >
            {children}
        </span>
    );
}
