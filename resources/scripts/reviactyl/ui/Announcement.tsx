import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { DesignifyAlert } from '@/state/designify';
import Md2React from '@/reviactyl/ui/Md2React';
import styled from 'styled-components';
import tw from 'twin.macro';
import { FaBullhorn, FaCircleXmark, FaCircleInfo, FaTriangleExclamation, FaCircleCheck } from 'react-icons/fa6';

const Container = styled.div`
    ${tw`px-2`}
`;

const AlertContainer = styled.div`
    ${tw`mx-auto w-full flex items-center gap-x-3 max-w-[1200px] p-3 mt-2 rounded-ui text-gray-100 border`}
`;

const getAlertClass = (type: string): string =>
    type === 'info'
        ? 'bg-blue-800/10 border-blue-500/60'
        : type === 'announcement'
        ? 'bg-reviactyl/10 border-reviactyl/60'
        : type === 'danger'
        ? 'bg-danger/10 border-danger/60'
        : type === 'success'
        ? 'bg-success/10 border-success/60'
        : type === 'warning'
        ? 'bg-yellow-800/10 border-yellow-500/60'
        : '';

const getAlertIcon = (type: string) =>
    type === 'info' ? (
        <FaCircleInfo className='h-5 w-5 font-bold !text-blue-500' />
    ) : type === 'announcement' ? (
        <FaBullhorn className='h-5 w-5 font-bold !text-reviactyl' />
    ) : type === 'danger' ? (
        <FaCircleXmark className='h-5 w-5 font-bold !text-danger/50' />
    ) : type === 'success' ? (
        <FaCircleCheck className='h-5 w-5 font-bold !text-success/50' />
    ) : type === 'warning' ? (
        <FaTriangleExclamation className='h-5 w-5 font-bold !text-yellow-500' />
    ) : (
        ''
    );

const Announcement = () => {
    const reviactyl = useStoreState((state: ApplicationStore) => state.designify.data);
    const configuredAlerts = reviactyl?.alerts ?? [];
    const fallbackAlertType = reviactyl?.alertType;
    const fallbackAlertMessage = reviactyl?.alertMessage;
    const alerts: DesignifyAlert[] =
        configuredAlerts.length > 0
            ? configuredAlerts
            : fallbackAlertType && fallbackAlertMessage
            ? [{ type: fallbackAlertType, message: fallbackAlertMessage }]
            : [];

    return (
        <Container>
            {alerts
                .filter((alert) => alert.type !== 'disabled')
                .map((alert, index) => (
                    <AlertContainer
                        key={`${index}-${alert.type}-${alert.message.slice(0, 20)}`}
                        className={getAlertClass(alert.type)}
                    >
                        <div>{getAlertIcon(alert.type)}</div>
                        <div>
                            <Md2React markdown={alert.message} />
                        </div>
                    </AlertContainer>
                ))}
        </Container>
    );
};

export default Announcement;
