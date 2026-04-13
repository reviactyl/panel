import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import getSocialLogins, { SocialLogin } from '@/api/account/getSocialLogins';
import unlinkSocialLogin from '@/api/account/unlinkSocialLogin';
import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import { Button } from '@/reviactyl/elements/button';
import tw from 'twin.macro';
import { format } from 'date-fns';
import { FaGoogle, FaDiscord, FaGithub, FaLink, FaUnlink, FaCheckCircle, FaTimesCircle } from 'react-icons/fa';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import { Dialog } from '@/reviactyl/elements/dialog';
import styled from 'styled-components';
import { Trans } from 'react-i18next';

const ProviderIcon = ({ provider }: { provider: string }) => {
    switch (provider) {
        case 'google':
            return <FaGoogle />;
        case 'discord':
            return <FaDiscord />;
        case 'github':
            return <FaGithub />;
        default:
            return <FaLink />;
    }
};

const Container = styled.div`
    ${tw`grid grid-cols-1 gap-4`}
`;

const Item = styled.div`
    ${tw`flex items-center justify-between p-4 rounded-ui border border-gray-600`}
`;

export default () => {
    const { t } = useTranslation('dashboard/account');
    const [logins, setLogins] = useState<SocialLogin[]>([]);
    const [loading, setLoading] = useState(true);
    const [unlinkProvider, setUnlinkProvider] = useState<string | null>(null);

    const socialSettings = window.SocialLoginConfiguration || { google: false, discord: false, github: false };
    const enabledProviders = Object.keys(socialSettings).filter(
        (k) => socialSettings[k as keyof typeof socialSettings]
    );

    useEffect(() => {
        getSocialLogins()
            .then(setLogins)
            .catch((error) => {
                console.error(error);
            })
            .finally(() => setLoading(false));
    }, []);

    const onUnlink = () => {
        if (!unlinkProvider) return;

        unlinkSocialLogin(unlinkProvider)
            .then(() => {
                setLogins((s) => s.filter((l) => l.provider !== unlinkProvider));
            })
            .catch((error) => {
                console.error(error);
            })
            .finally(() => setUnlinkProvider(null));
    };

    if (enabledProviders.length === 0) return null;

    return (
        <TitledGreyBox title={t('overview.social.title')}>
            <SpinnerOverlay visible={loading} />
            <Dialog.Confirm
                open={!!unlinkProvider}
                onClose={() => setUnlinkProvider(null)}
                title={t('overview.social.unlink.title')}
                confirm={t('overview.social.unlink.button')}
                onConfirmed={onUnlink}
            >
                <Trans
                    i18nKey={'dashboard/account:overview.social.unlink.confirm'}
                    values={{ provider: unlinkProvider }}
                >
                    Are you sure you want to unlink your <b>{unlinkProvider}</b> account? You will no longer be able to
                    use it to log in.
                </Trans>
            </Dialog.Confirm>

            <Container>
                {enabledProviders.map((provider) => {
                    const login = logins.find((l) => l.provider === provider);
                    const isLinked = !!login;

                    return (
                        <Item key={provider}>
                            <div css={tw`flex items-center`}>
                                <div css={tw`text-2xl w-10 text-center text-gray-300`}>
                                    <ProviderIcon provider={provider} />
                                </div>
                                <div css={tw`ml-4`}>
                                    <p css={tw`font-bold capitalize`}>{provider}</p>
                                    <div css={tw`text-xs text-gray-400 mt-1`}>
                                        {isLinked ? (
                                            <span css={tw`text-green-400 flex items-center`}>
                                                <FaCheckCircle css={tw`mr-1`} />
                                                {t('overview.social.status.connected', {
                                                    date: format(login.updatedAt, t('overview.social.date_format')),
                                                })}
                                            </span>
                                        ) : (
                                            <span css={tw`text-gray-500 flex items-center`}>
                                                <FaTimesCircle css={tw`mr-1`} />
                                                {t('overview.social.status.not-connected')}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                            <div>
                                {isLinked ? (
                                    <Button.Danger
                                        size={Button.Sizes.Small}
                                        onClick={() => setUnlinkProvider(provider)}
                                    >
                                        <FaUnlink css={tw`mr-2`} />
                                        {t('overview.social.actions.unlink')}
                                    </Button.Danger>
                                ) : (
                                    <a href={`/auth/login/${provider}`} css={tw`no-underline`}>
                                        <Button size={Button.Sizes.Small}>
                                            <FaLink css={tw`mr-2`} />
                                            {t('overview.social.actions.link')}
                                        </Button>
                                    </a>
                                )}
                            </div>
                        </Item>
                    );
                })}
            </Container>
        </TitledGreyBox>
    );
};
