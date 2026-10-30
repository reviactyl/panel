import React from 'react';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';

export const ContentContainer = ({ children, className = '' }: React.HTMLAttributes<HTMLDivElement>) => {
    const layoutType = useStoreState((state: ApplicationStore) => state.designify.data!.layoutType);
    return (
        <div className={`flex pe-1 lg:ps-62.5 ${layoutType !== 'modern' ? 'pt-16' : ''} ${className}`}>
            {children}
        </div>
    );
};
