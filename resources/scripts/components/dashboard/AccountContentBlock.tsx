import React from 'react';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import PageContentBlock, { PageContentBlockProps } from '@/reviactyl/elements/PageContentBlock';
import PageHeader from '@/reviactyl/ui/PageHeader';

interface Props extends PageContentBlockProps {
    title: string;
}

const AccountContentBlock = ({ title, children, ...props }: Props) => {
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);

    return (
        <PageContentBlock inset title={`${title} | ${name}`} {...props}>
            <PageHeader title={title} />
            {children}
        </PageContentBlock>
    );
};

export default AccountContentBlock;
