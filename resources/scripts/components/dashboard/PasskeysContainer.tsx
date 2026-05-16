import ContentBlock from '@/reviactyl/ui/ContentBlock';
import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import PasskeysForm from '@/components/dashboard/forms/PasskeysForm';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('dashboard/account');

    return (
        <ContentBlock title={t('overview.passkeys')}>
            <TitledGreyBox title={t('overview.passkeys')} showFlashes={'account:passkeys'}>
                <PasskeysForm />
            </TitledGreyBox>
        </ContentBlock>
    );
};
