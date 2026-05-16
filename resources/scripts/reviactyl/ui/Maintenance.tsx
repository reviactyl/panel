import { useState } from 'react';
import PageContentBlock from '@/reviactyl/elements/PageContentBlock';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import Card from '@/reviactyl/ui/Card';
import Title from '@/reviactyl/ui/Title';
import tw from 'twin.macro';
import styled from 'styled-components';
import { LogoContainer } from '@/reviactyl/ui/LogoContainer';
import logout from '@/api/auth/logout';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import { LogoutIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';

const Container = styled.div`
    ${tw`my-auto mx-auto`}
`;

const CardContainer = styled.div`
    ${tw`max-w-[28.125rem] w-screen pt-10`}
`;

export default () => {
    const { t } = useTranslation('strings');
    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.logo);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const maintenance = useStoreState((state) => state.designify.data!.maintenance);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const onTriggerLogout = () => {
        setIsLoggingOut(true);
        logout().finally(() => {
            window.location.href = '/';
        });
    };
    return (
        <PageContentBlock className='flex flex-col h-full' title={t('under_maintenance')} showFlashKey={'dashboard'}>
            <Container>
                <CardContainer>
                    <LogoContainer>
                        <img src={logo} alt={name} css={tw`h-[3rem]`} />
                    </LogoContainer>
                    <Card>
                        <SpinnerOverlay visible={isLoggingOut} />
                        <Title className='text-3xl text-center pb-3'>{t('under_maintenance')}</Title>
                        <p className='text-center'>{maintenance}</p>
                    </Card>
                    <button className='flex items-center mx-auto mt-2' onClick={onTriggerLogout}>
                        <span className='text-danger/80'>{t('logout')}</span>{' '}
                        <LogoutIcon className='w-5 h-5 text-danger/80' />
                    </button>
                </CardContainer>
            </Container>
        </PageContentBlock>
    );
};
