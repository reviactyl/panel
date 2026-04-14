import PageContentBlock, { PageContentBlockProps } from '@/reviactyl/elements/PageContentBlock';
import React from 'react';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import Title from '@/reviactyl/ui/Title';

interface Props extends PageContentBlockProps {
    title: string;
    description?: string;
    children?: React.ReactNode;
}

const ContentBlock = ({ title, description, children, ...props }: Props) => {
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);

    return (
        <PageContentBlock title={`${title} | ${name}`} {...props}>
            <Title className='text-4xl mb-2'>{title}</Title>
            <p className='text-xs text-gray-500'>{description}</p>
            {children}
        </PageContentBlock>
    );
};

export default ContentBlock;
