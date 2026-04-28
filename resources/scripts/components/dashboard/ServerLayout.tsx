import React from 'react';
import ServerGrid from '@/reviactyl/components/layouts/ServerGrid';
import ServerRow from '@/reviactyl/components/layouts/ServerRow';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';

type Props = React.ComponentProps<typeof ServerGrid> & React.ComponentProps<typeof ServerRow>;

export const ServerLayout: React.FC<Props> = ({ ...props }) => {
    const cardType = useStoreState((state: ApplicationStore) => state.reviactyl.data!.cardType);
    return <div>{cardType === 'grid' ? <ServerGrid {...props} /> : <ServerRow {...props} />}</div>;
};

export const LayoutContainer = ({ children }: { children: React.ReactNode }) => {
    const cardType = useStoreState((state: ApplicationStore) => state.reviactyl.data!.cardType);
    return (
        <>{cardType === 'grid' ? <div className='grid lg:grid-cols-2 gap-4'>{children}</div> : <div>{children}</div>}</>
    );
};
