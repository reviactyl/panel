import { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import getSocialLogins, { SocialLogin } from '@/api/account/getSocialLogins';
import unlinkSocialLogin from '@/api/account/unlinkSocialLogin';
import TitledGreyBox from '@/reviactyl/elements/TitledGreyBox';
import { Button } from '@/reviactyl/components/button';
import { format } from 'date-fns';
import { FaLink, FaUnlink, FaCheckCircle, FaTimesCircle, FaGoogle, FaDiscord, FaGithub } from 'react-icons/fa';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import { Dialog } from '@/reviactyl/elements/dialog';
import { Trans } from 'react-i18next';
import useFlash from '@/plugins/useFlash';
import { httpErrorToHuman } from '@/api/http';

const flashKey = 'account:social-logins';

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

export default () => {
    const { t } = useTranslation('dashboard/account');
    const [logins, setLogins] = useState<SocialLogin[] | null>(null);
    const [loading, setLoading] = useState(true);
    const [unlinkProvider, setUnlinkProvider] = useState<string | null>(null);
    const { addError, clearFlashes } = useFlash();

    const socialSettings = window.SocialLoginConfiguration || { google: false, discord: false, github: false };
    const enabledProviders = Object.keys(socialSettings).filter(
        (k) => socialSettings[k as keyof typeof socialSettings],
    );

    const loadLogins = useCallback(() => {
        setLoading(true);
        clearFlashes(flashKey);
        getSocialLogins()
            .then(setLogins)
            .catch((error) => {
                addError({ key: flashKey, message: httpErrorToHuman(error) });
            })
            .finally(() => setLoading(false));
    }, [addError, clearFlashes]);

    useEffect(() => {
        if (enabledProviders.length > 0) loadLogins();
    }, [loadLogins, enabledProviders.length]);

    const onUnlink = () => {
        if (!unlinkProvider) return;

        setLoading(true);
        clearFlashes(flashKey);
        unlinkSocialLogin(unlinkProvider)
            .then(() => {
                setLogins((s) => s && s.filter((l) => l.provider !== unlinkProvider));
            })
            .catch((error) => {
                addError({ key: flashKey, message: httpErrorToHuman(error) });
            })
            .finally(() => {
                setLoading(false);
                setUnlinkProvider(null);
            });
    };

    if (enabledProviders.length === 0) return null;

    return (
        <TitledGreyBox icon={FaLink} title={t('overview.social.title')} showFlashes={flashKey}>
            <SpinnerOverlay visible={loading} />
            {!loading && logins === null && <Button onClick={loadLogins}>{t('notifications:retry')}</Button>}
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

            <div className='grid grid-cols-1 gap-4'>
                {logins !== null &&
                    enabledProviders.map((provider) => {
                        const login = logins.find((l) => l.provider === provider);
                        const isLinked = !!login;

                        return (
                            <div
                                key={provider}
                                className='flex items-center justify-between rounded-ui border border-gray-800 p-4'
                            >
                                <div className='flex items-center'>
                                    <div className='w-10 text-center text-2xl text-gray-300'>
                                        <ProviderIcon provider={provider} />
                                    </div>
                                    <div className='ml-4'>
                                        <p className='font-bold capitalize'>{provider}</p>
                                        <div className='mt-1 text-xs text-gray-400'>
                                            {isLinked ? (
                                                <span className='flex items-center text-green-400'>
                                                    <FaCheckCircle className='mr-1' />
                                                    {t('overview.social.status.connected', {
                                                        date: format(login.updatedAt, t('overview.social.date_format')),
                                                    })}
                                                </span>
                                            ) : (
                                                <span className='flex items-center text-muted'>
                                                    <FaTimesCircle className='mr-1' />
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
                                            <FaUnlink className='mr-2' />
                                            {t('overview.social.actions.unlink')}
                                        </Button.Danger>
                                    ) : (
                                        <a href={`/auth/login/${provider}`} className='no-underline'>
                                            <Button size={Button.Sizes.Small}>
                                                <FaLink className='mr-2' />
                                                {t('overview.social.actions.link')}
                                            </Button>
                                        </a>
                                    )}
                                </div>
                            </div>
                        );
                    })}
            </div>
        </TitledGreyBox>
    );
};
