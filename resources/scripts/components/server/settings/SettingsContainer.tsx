import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import { ServerContext } from '@/state/server';
import { useStoreState } from 'easy-peasy';
import RenameServerBox from '@/components/server/settings/RenameServerBox';
import FlashMessageRender from '@/components/FlashMessageRender';
import Can from '@/reviactyl/elements/Can';
import ReinstallServerBox from '@/components/server/settings/ReinstallServerBox';
import TimezoneBox from '@/components/server/settings/TimezoneBox';
import Input from '@/reviactyl/elements/Input';
import Label from '@/reviactyl/elements/Label';
import ServerContentBlock from '@/reviactyl/elements/ServerContentBlock';
import isEqual from 'react-fast-compare';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';
import { ip } from '@/lib/formatters';
import { Button } from '@/reviactyl/components/button/index';
import { useTranslation } from 'react-i18next';
import { FaFolderOpen } from 'react-icons/fa';
import { FaGaugeHigh } from 'react-icons/fa6';

export default () => {
    const { t } = useTranslation('server/settings');
    const username = useStoreState((state) => state.user.data!.username);
    // SFTP daemons require the legacy eight-character UUID, even when Panel URLs use serv_ identifiers.
    const uuidShort = ServerContext.useStoreState((state) => state.server.data!.__deprecatedUuidShort);
    const sftpUsername = `${username}.${uuidShort}`;
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const node = ServerContext.useStoreState((state) => state.server.data!.node);
    const sftp = ServerContext.useStoreState((state) => state.server.data!.sftpDetails, isEqual);

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey='settings' className='mb-4' />
            <div className='flex flex-col gap-6 md:flex-row md:gap-10'>
                <div className='flex w-full min-w-0 flex-col gap-6 md:flex-1 md:gap-10'>
                    <Can action={'file.sftp'}>
                        <TitledGreyBox icon={FaFolderOpen} title={t('sftp.title')}>
                            <div>
                                <Label>{t('sftp.address')}</Label>
                                <CopyOnClick text={`sftp://${ip(sftp.ip)}:${sftp.port}`}>
                                    <Input
                                        dir='ltr'
                                        type={'text'}
                                        value={`sftp://${ip(sftp.ip)}:${sftp.port}`}
                                        readOnly
                                    />
                                </CopyOnClick>
                            </div>
                            <div className='mt-6'>
                                <Label>{t('sftp.username')}</Label>
                                <CopyOnClick text={sftpUsername}>
                                    <Input dir='ltr' type={'text'} value={sftpUsername} readOnly />
                                </CopyOnClick>
                            </div>
                            <div className='mt-6 flex items-center'>
                                <div className='flex-1'>
                                    <div className='border-l-4 border-reviactyl p-3'>
                                        <p className='text-xs text-gray-200'>{t('sftp.password')}</p>
                                    </div>
                                </div>
                                <div className='ml-4'>
                                    <a href={`sftp://${sftpUsername}@${ip(sftp.ip)}:${sftp.port}`}>
                                        <Button.Text variant={Button.Variants.Secondary}>
                                            {t('sftp.button')}
                                        </Button.Text>
                                    </a>
                                </div>
                            </div>
                        </TitledGreyBox>
                    </Can>
                    <TitledGreyBox icon={FaGaugeHigh} title={t('info.title')}>
                        <div className='flex items-center justify-between text-sm'>
                            <p>{t('info.node')}</p>
                            <code className='rounded-ui border border-gray-800 bg-gray-900 px-2 py-1 font-mono'>
                                {node}
                            </code>
                        </div>
                        <CopyOnClick text={uuid}>
                            <div className='mt-2 flex items-center justify-between gap-4 text-sm'>
                                <p className='shrink-0'>{t('info.server')}</p>
                                <code className='min-w-0 wrap-anywhere rounded-ui border border-gray-800 bg-gray-900 px-2 py-1 text-right font-mono'>
                                    {uuid}
                                </code>
                            </div>
                        </CopyOnClick>
                        <div className='mt-6'>
                            <Label>{t('info.public-status-page')}</Label>
                            <CopyOnClick text={`${window.location.origin}/status/${uuid}`}>
                                <Input
                                    dir='ltr'
                                    type={'text'}
                                    value={`${window.location.origin}/status/${uuid}`}
                                    readOnly
                                />
                            </CopyOnClick>
                        </div>
                    </TitledGreyBox>
                </div>
                <Can action={['settings.rename', 'settings.timezone']} matchAny>
                    <div className='flex w-full min-w-0 flex-col gap-6 md:flex-1 md:gap-10'>
                        <Can action={'settings.rename'}>
                            <RenameServerBox />
                        </Can>
                        <Can action={'settings.timezone'}>
                            <TimezoneBox />
                        </Can>
                    </div>
                </Can>
            </div>
            <Can action={'settings.reinstall'}>
                <div className='mt-6 md:mt-10'>
                    <ReinstallServerBox />
                </div>
            </Can>
        </ServerContentBlock>
    );
};
