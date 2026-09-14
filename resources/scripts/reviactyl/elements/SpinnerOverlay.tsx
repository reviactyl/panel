import React from 'react';
import Spinner, { SpinnerSize } from '@/reviactyl/elements/Spinner';
import Fade from '@/reviactyl/elements/Fade';

interface Props {
    visible: boolean;
    fixed?: boolean;
    size?: SpinnerSize;
    backgroundOpacity?: number;
    children?: React.ReactNode;
}

const SpinnerOverlay = ({ size, fixed, visible, backgroundOpacity, children }: Props) => (
    <Fade timeout={150} in={visible} unmountOnExit>
        <div
            className={`top-0 left-0 z-40 flex h-full w-full flex-col items-center justify-center rounded ${
                fixed ? 'fixed' : 'absolute'
            }`}
            style={{ background: `rgba(0, 0, 0, ${backgroundOpacity || 0.45})` }}
        >
            <Spinner size={size} />
            {children && (typeof children === 'string' ? <p className='mt-4 text-gray-400'>{children}</p> : children)}
        </div>
    </Fade>
);

export default SpinnerOverlay;
