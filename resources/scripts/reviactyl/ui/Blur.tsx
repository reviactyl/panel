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
            className={`${
                allocationBlur
                    ? 'blur-sm transition-[filter] duration-250 ease-out hover:blur-none motion-reduce:transition-none'
                    : 'blur-none'
            } inline-block max-w-full truncate align-bottom ${className}`}
        >
            {children}
        </span>
    );
}
