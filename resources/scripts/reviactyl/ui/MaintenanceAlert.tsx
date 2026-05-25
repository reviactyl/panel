import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import styled from 'styled-components';
import tw from 'twin.macro';
import { useTranslation } from 'react-i18next';
import { FaTriangleExclamation } from 'react-icons/fa6';

const Container = styled.div`
    ${tw`px-2`}
`;

const AlertContainer = styled.div`
    ${tw`mx-auto w-full flex items-center gap-x-3 max-w-[1200px] p-3 mt-2 rounded-ui text-gray-100 border`}
`;

const MaintenanceAlert = () => {
    const { t } = useTranslation('strings');
    const isUnderMaintenance = useStoreState((state: ApplicationStore) => state.designify.data!.isUnderMaintenance);
    return (
        <>
            {isUnderMaintenance ? (
                <Container>
                    <AlertContainer className={`bg-yellow-800/10 border-yellow-500/60`}>
                        <div>
                            <FaTriangleExclamation className='h-5 w-5 font-bold !text-yellow-500' />
                        </div>
                        <div>
                            <b>{t('under_maintenance')}</b> {t('maintenance-mode-warning')}
                        </div>
                    </AlertContainer>
                </Container>
            ) : (
                ''
            )}
        </>
    );
};

export default MaintenanceAlert;
