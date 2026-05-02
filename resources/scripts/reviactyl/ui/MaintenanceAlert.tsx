import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { ExclamationIcon } from '@heroicons/react/solid';
import styled from 'styled-components';
import tw from 'twin.macro';

const Container = styled.div`
    ${tw`px-2`}
`;

const AlertContainer = styled.div`
    ${tw`mx-auto w-full flex items-center gap-x-3 max-w-[1200px] p-3 mt-2 rounded-ui text-gray-100 !border-t-0 !border-r-0 !border-b-0 !border-l-4`}
`;

const MaintenanceAlert = () => {
    const isUnderMaintenance = useStoreState((state: ApplicationStore) => state.designify.data!.isUnderMaintenance);
    return (
        <>
            {isUnderMaintenance ? (
                <Container>
                    <AlertContainer className={`bg-yellow-500/10 border-yellow-500`}>
                        <div>
                            <ExclamationIcon className='h-5 w-5 font-bold !text-yellow-500' />
                        </div>
                        <div>
                            <b>Maintenance Mode!</b> Your clients will be unable to access Reviactyl panel until you
                            disable Maintenance mode.
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
