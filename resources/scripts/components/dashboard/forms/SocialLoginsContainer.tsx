import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import getSocialLogins, { SocialLogin } from '@/api/account/getSocialLogins';
import unlinkSocialLogin from '@/api/account/unlinkSocialLogin';
import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import { Button } from '@/reviactyl/components/button';
import tw from 'twin.macro';
import { format } from 'date-fns';
import { FaLink, FaUnlink, FaCheckCircle, FaTimesCircle } from 'react-icons/fa';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import { Dialog } from '@/reviactyl/elements/dialog';
import styled from 'styled-components';
import { Trans } from 'react-i18next';

const GoogleIcon = () => (
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24'>
        <path
            d='M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z'
            fill='#4285F4'
        />
        <path
            d='M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z'
            fill='#34A853'
        />
        <path
            d='M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z'
            fill='#FBBC05'
        />
        <path
            d='M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z'
            fill='#EA4335'
        />
    </svg>
);

const DiscordIcon = () => (
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' fill='#5865F2'>
        <path d='M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057.1 18.08.118 18.1.137 18.11a19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z' />
    </svg>
);

const GitHubIcon = () => (
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' fill='currentColor'>
        <path d='M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12' />
    </svg>
);

const ProviderIcon = ({ provider }: { provider: string }) => {
    switch (provider) {
        case 'google':
            return <GoogleIcon />;
        case 'discord':
            return <DiscordIcon />;
        case 'github':
            return <GitHubIcon />;
        default:
            return <FaLink />;
    }
};

const Container = styled.div`
    ${tw`grid grid-cols-1 gap-4`}
`;

const Item = styled.div`
    ${tw`flex items-center justify-between p-4 rounded-ui border border-gray-800`}
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
                                            <span css={tw`text-gray-600 flex items-center`}>
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
