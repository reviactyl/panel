import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import { ServerContext } from '@/state/server';
import { useStoreState } from 'easy-peasy';
import RenameServerBox from '@/components/server/settings/RenameServerBox';
import FlashMessageRender from '@/components/FlashMessageRender';
import Can from '@/reviactyl/elements/Can';
import ReinstallServerBox from '@/components/server/settings/ReinstallServerBox';
import Input from '@/reviactyl/elements/Input';
import Label from '@/reviactyl/elements/Label';
import ServerContentBlock from '@/reviactyl/elements/ServerContentBlock';
import isEqual from 'react-fast-compare';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';
import { ip } from '@/lib/formatters';
import { Button } from '@/reviactyl/components/button/index';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('server/settings');
    const username = useStoreState((state) => state.user.data!.username);
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const node = ServerContext.useStoreState((state) => state.server.data!.node);
    const sftp = ServerContext.useStoreState((state) => state.server.data!.sftpDetails, isEqual);

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey='settings' className='mb-4' />
            <div className='md:flex'>
                <div className='w-full md:mr-10 md:flex-1'>
                    <Can action={'file.sftp'}>
                        <TitledGreyBox title={t('sftp.title')} className='mb-6 md:mb-10'>
                            <div>
                                <Label>{t('sftp.address')}</Label>
                                <CopyOnClick text={`sftp://${ip(sftp.ip)}:${sftp.port}`}>
                                    <Input type={'text'} value={`sftp://${ip(sftp.ip)}:${sftp.port}`} readOnly />
                                </CopyOnClick>
                            </div>
                            <div className='mt-6'>
                                <Label>{t('sftp.username')}</Label>
                                <CopyOnClick text={`${username}.${id}`}>
                                    <Input type={'text'} value={`${username}.${id}`} readOnly />
                                </CopyOnClick>
                            </div>
                            <div className='mt-6 flex items-center'>
                                <div className='flex-1'>
                                    <div className='border-l-4 border-reviactyl p-3'>
                                        <p className='text-xs text-gray-200'>{t('sftp.password')}</p>
                                    </div>
                                </div>
                                <div className='ml-4'>
                                    <a href={`sftp://${username}.${id}@${ip(sftp.ip)}:${sftp.port}`}>
                                        <Button.Text variant={Button.Variants.Secondary}>
                                            {t('sftp.button')}
                                        </Button.Text>
                                    </a>
                                </div>
                            </div>
                        </TitledGreyBox>
                    </Can>
                    <TitledGreyBox title={t('info.title')} className='mb-6 md:mb-10'>
                        <div className='flex items-center justify-between text-sm'>
                            <p>{t('info.node')}</p>
                            <code className='rounded-ui border border-gray-800 bg-gray-900 px-2 py-1 font-mono'>
                                {node}
                            </code>
                        </div>
                        <CopyOnClick text={uuid}>
                            <div className='mt-2 flex items-center justify-between text-sm'>
                                <p>{t('info.server')}</p>
                                <code className='rounded-ui border border-gray-800 bg-gray-900 px-2 py-1 font-mono'>
                                    {uuid}
                                </code>
                            </div>
                        </CopyOnClick>
                        <div className='mt-6'>
                            <Label>{t('info.public-status-page')}</Label>
                            <CopyOnClick text={`${window.location.origin}/status/${uuid}`}>
                                <Input type={'text'} value={`${window.location.origin}/status/${uuid}`} readOnly />
                            </CopyOnClick>
                        </div>
                    </TitledGreyBox>
                </div>
                <div className='mt-6 w-full md:mt-0 md:flex-1'>
                    <Can action={'settings.rename'}>
                        <div className='mb-6 md:mb-10'>
                            <RenameServerBox />
                        </div>
                    </Can>
                    <Can action={'settings.reinstall'}>
                        <ReinstallServerBox />
                    </Can>
                </div>
            </div>
        </ServerContentBlock>
    );
};
