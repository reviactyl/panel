import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import { ServerContext } from '@/state/server';
import { useStoreState } from 'easy-peasy';
import RenameServerBox from '@/components/server/settings/RenameServerBox';
import FlashMessageRender from '@/components/FlashMessageRender';
import Can from '@/reviactyl/elements/Can';
import ReinstallServerBox from '@/components/server/settings/ReinstallServerBox';
import tw from 'twin.macro';
import Input from '@/reviactyl/elements/Input';
import Label from '@/reviactyl/elements/Label';
import ServerContentBlock from '@/reviactyl/elements/ServerContentBlock';
import isEqual from 'react-fast-compare';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';
import { ip } from '@/lib/formatters';
import { Button } from '@/reviactyl/elements/button/index';
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
            <FlashMessageRender byKey={'settings'} css={tw`mb-4`} />
            <div css={tw`md:flex`}>
                <div css={tw`w-full md:flex-1 md:mr-10`}>
                    <Can action={'file.sftp'}>
                        <TitledGreyBox title={t('sftp.title')} css={tw`mb-6 md:mb-10`}>
                            <div>
                                <Label>{t('sftp.address')}</Label>
                                <CopyOnClick text={`sftp://${ip(sftp.ip)}:${sftp.port}`}>
                                    <Input type={'text'} value={`sftp://${ip(sftp.ip)}:${sftp.port}`} readOnly />
                                </CopyOnClick>
                            </div>
                            <div css={tw`mt-6`}>
                                <Label>{t('sftp.username')}</Label>
                                <CopyOnClick text={`${username}.${id}`}>
                                    <Input type={'text'} value={`${username}.${id}`} readOnly />
                                </CopyOnClick>
                            </div>
                            <div css={tw`mt-6 flex items-center`}>
                                <div css={tw`flex-1`}>
                                    <div css={tw`border-l-4 border-cyan-500 p-3`}>
                                        <p css={tw`text-xs text-gray-200`}>{t('sftp.password')}</p>
                                    </div>
                                </div>
                                <div css={tw`ml-4`}>
                                    <a href={`sftp://${username}.${id}@${ip(sftp.ip)}:${sftp.port}`}>
                                        <Button.Text variant={Button.Variants.Secondary}>
                                            {t('sftp.button')}
                                        </Button.Text>
                                    </a>
                                </div>
                            </div>
                        </TitledGreyBox>
                    </Can>
                    <TitledGreyBox title={t('info.title')} css={tw`mb-6 md:mb-10`}>
                        <div css={tw`flex items-center justify-between text-sm`}>
                            <p>{t('info.node')}</p>
                            <code css={tw`font-mono bg-gray-900 rounded-ui border border-gray-800 py-1 px-2`}>
                                {node}
                            </code>
                        </div>
                        <CopyOnClick text={uuid}>
                            <div css={tw`flex items-center justify-between mt-2 text-sm`}>
                                <p>{t('info.server')}</p>
                                <code css={tw`font-mono bg-gray-900 rounded-ui border border-gray-800 py-1 px-2`}>
                                    {uuid}
                                </code>
                            </div>
                        </CopyOnClick>
                        <div css={tw`mt-6`}>
                            <Label>{t('info.public-status-page')}</Label>
                            <CopyOnClick text={`${window.location.origin}/status/${uuid}`}>
                                <Input type={'text'} value={`${window.location.origin}/status/${uuid}`} readOnly />
                            </CopyOnClick>
                        </div>
                    </TitledGreyBox>
                </div>
                <div css={tw`w-full mt-6 md:flex-1 md:mt-0`}>
                    <Can action={'settings.rename'}>
                        <div css={tw`mb-6 md:mb-10`}>
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
