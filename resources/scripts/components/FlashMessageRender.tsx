import React from 'react';
import MessageBox from '@/components/MessageBox';
import { useStoreState } from 'easy-peasy';

type Props = Readonly<{
    byKey?: string;
    excludeKeys?: string[];
    className?: string;
}>;

const FlashMessageRender = ({ byKey, excludeKeys = [], className }: Props) => {
    const flashes = useStoreState((state) =>
        state.flashes.items.filter((flash) =>
            byKey ? flash.key === byKey : !flash.key || !excludeKeys.includes(flash.key),
        ),
    );

    return flashes.length ? (
        <div className={className}>
            {flashes.map((flash, index) => (
                <React.Fragment key={flash.id || flash.type + flash.message}>
                    {index > 0 && <div className='mt-2' />}
                    <MessageBox type={flash.type} title={flash.title}>
                        {flash.message}
                    </MessageBox>
                </React.Fragment>
            ))}
        </div>
    ) : null;
};

export default FlashMessageRender;
