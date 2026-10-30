import { CreatePasskeysForm, ListPasskeysForm } from '@/components/dashboard/forms/PasskeysForm';
import ContentBox from '@/reviactyl/elements/ContentBox';
import { useTranslation } from 'react-i18next';
import AccountContentBlock from '@/components/dashboard/AccountContentBlock';

export default () => {
    const { t } = useTranslation('dashboard/account');

    return (
        <AccountContentBlock title={t('overview.passkeys')}>
            <div className='grid grid-cols-1 items-start gap-6 lg:grid-cols-2'>
                <ContentBox
                    insetTitle
                    title={t('passkeys.register')}
                    className='min-w-0'
                    showFlashes={'account:passkeys'}
                >
                    <CreatePasskeysForm />
                </ContentBox>
                <ContentBox
                    insetTitle
                    title={t('overview.passkeys')}
                    className='min-w-0'
                    showFlashes={'account:passkeys'}
                >
                    <ListPasskeysForm />
                </ContentBox>
            </div>
        </AccountContentBlock>
    );
};
