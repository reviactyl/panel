import PageContentBlock, { PageContentBlockProps } from '@/reviactyl/elements/PageContentBlock';
import React from 'react';
import { ServerContext } from '@/state/server';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import Title from '@/reviactyl/ui/Title';

interface Props extends PageContentBlockProps {
    title: string;
    children?: React.ReactNode;
}

const ServerContentBlock = ({ title, children, ...props }: Props) => {
    const servername = ServerContext.useStoreState((state) => state.server.data!.name);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);

    return (
        <PageContentBlock title={`(${servername}) ${title} | ${name}`} {...props}>
            <Title className='text-4xl mb-2'>{title}</Title>
            {children}
        </PageContentBlock>
    );
};

export default ServerContentBlock;
