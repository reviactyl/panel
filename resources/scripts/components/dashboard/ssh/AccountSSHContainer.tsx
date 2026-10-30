import { useEffect } from 'react';
import ContentBox from '@/reviactyl/elements/ContentBox';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import FlashMessageRender from '@/components/FlashMessageRender';
import AccountContentBlock from '@/components/dashboard/AccountContentBlock';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import { useSSHKeys } from '@/api/account/ssh-keys';
import { useFlashKey } from '@/plugins/useFlash';
import { FaKey } from 'react-icons/fa6';
import { format } from 'date-fns';
import CreateSSHKeyForm from '@/components/dashboard/ssh/CreateSSHKeyForm';
import DeleteSSHKeyButton from '@/components/dashboard/ssh/DeleteSSHKeyButton';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('dashboard/account');
    const { clearAndAddHttpError } = useFlashKey('account');
    const { data, isValidating, error } = useSSHKeys({
        revalidateOnMount: true,
        revalidateOnFocus: false,
    });

    useEffect(() => {
        clearAndAddHttpError(error);
    }, [error]);

    return (
        <AccountContentBlock title={t('ssh.title')}>
            <FlashMessageRender byKey={'account'} />
            <div className='grid grid-cols-1 items-start gap-6 lg:grid-cols-2'>
                <ContentBox insetTitle title={t('ssh.add-key')} className='min-w-0'>
                    <CreateSSHKeyForm />
                </ContentBox>
                <ContentBox insetTitle title={t('ssh.title')} className='min-w-0'>
                    <SpinnerOverlay visible={!data && isValidating} />
                    {!data || !data.length ? (
                        <p className='text-center text-sm'>{!data ? t('overview.loading') : t('ssh.not-exist')}</p>
                    ) : (
                        data.map((key, index) => (
                            <GreyRowBox
                                key={key.fingerprint}
                                className={`flex space-x-4 items-center ${index > 0 ? ' mt-2' : ''}`}
                            >
                                <FaKey className={'text-gray-300'} />
                                <div className='flex-1'>
                                    <p className='text-sm wrap-break-word font-medium'>{key.name}</p>
                                    <p className='text-xs mt-1 font-mono truncate'>
                                        {t('ssh.fingerprint')}:{key.fingerprint}
                                    </p>
                                    <p className='text-xs mt-1 text-gray-300 uppercase'>
                                        {t('ssh.added-on')}:&nbsp;
                                        {format(key.createdAt, 'MMM do, yyyy HH:mm')}
                                    </p>
                                </div>
                                <DeleteSSHKeyButton name={key.name} fingerprint={key.fingerprint} />
                            </GreyRowBox>
                        ))
                    )}
                </ContentBox>
            </div>
        </AccountContentBlock>
    );
};
