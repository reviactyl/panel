import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import PageContentBlock, { PageContentBlockProps } from '@/reviactyl/elements/PageContentBlock';
import PageHeader from '@/reviactyl/ui/PageHeader';
import ContentBlock from '@/reviactyl/ui/ContentBlock';
import useModernHeaders from '@/reviactyl/ui/useModernHeaders';

interface Props extends PageContentBlockProps {
    title: string;
    classicHeading?: boolean;
}

const AccountContentBlock = ({ title, classicHeading = false, children, ...props }: Props) => {
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);

    const modernHeaders = useModernHeaders();

    if (!modernHeaders) {
        return classicHeading ? (
            <ContentBlock title={title} {...props}>
                {children}
            </ContentBlock>
        ) : (
            <PageContentBlock title={title} {...props}>
                {children}
            </PageContentBlock>
        );
    }

    return (
        <PageContentBlock inset title={`${title} | ${name}`} {...props}>
            <PageHeader title={title} />
            {children}
        </PageContentBlock>
    );
};

export default AccountContentBlock;
