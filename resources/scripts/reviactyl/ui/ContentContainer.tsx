import React from 'react';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';

export const ContentContainer: React.FC<React.PropsWithChildren> = ({ children }) => {
    const layoutType = useStoreState((state: ApplicationStore) => state.designify.data!.layoutType);
    return <div className={`flex pe-1 lg:ps-[250px] ${layoutType !== 'modern' ? 'pt-16' : ''}`}>{children}</div>;
};
