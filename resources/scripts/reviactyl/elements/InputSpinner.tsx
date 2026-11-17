import React from 'react';
import Spinner from '@/reviactyl/elements/Spinner';
import Fade from '@/reviactyl/elements/Fade';

const InputSpinner = ({ visible, children }: { visible: boolean; children: React.ReactNode }) => (
    <div className='relative'>
        <Fade appear unmountOnExit in={visible} timeout={150}>
            <div className='absolute end-0 flex h-full items-center justify-end pe-3'>
                <Spinner size={'small'} />
            </div>
        </Fade>
        {children}
    </div>
);

export default InputSpinner;
