import { CreatePasskeysForm, ListPasskeysForm } from '@/components/dashboard/forms/PasskeysForm';
import ContentBox from '@/reviactyl/elements/ContentBox';
import { useTranslation } from 'react-i18next';
import PageContentBlock from '@/reviactyl/elements/PageContentBlock';

export default () => {
    const { t } = useTranslation('dashboard/account');

    return (
        <PageContentBlock title={t('overview.passkeys')}>
            <div className='md:flex flex-nowrap my-10'>
                <ContentBox
                    title={t('passkeys.register')}
                    className='flex-none w-full md:w-1/2'
                    showFlashes={'account:passkeys'}
                >
                    <CreatePasskeysForm />
                </ContentBox>
                <ContentBox
                    title={t('overview.passkeys')}
                    className='flex-1 overflow-hidden mt-8 md:mt-0 md:ml-8'
                    showFlashes={'account:passkeys'}
                >
                    <ListPasskeysForm />
                </ContentBox>
            </div>
        </PageContentBlock>
    );
};
