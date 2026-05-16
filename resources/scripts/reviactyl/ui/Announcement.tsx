import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { DesignifyAlert } from '@/state/designify';
import Md2React from '@/reviactyl/ui/Md2React';
import { BellIcon, CheckIcon, ExclamationIcon, InboxInIcon, InformationCircleIcon } from '@heroicons/react/solid';
import styled from 'styled-components';
import tw from 'twin.macro';

const Container = styled.div`
    ${tw`px-2`}
`;

const AlertContainer = styled.div`
    ${tw`mx-auto w-full flex items-center gap-x-3 max-w-[1200px] p-3 mt-2 rounded-ui text-gray-100 !border-t-0 !border-r-0 !border-b-0 !border-l-4`}
`;

const getAlertClass = (type: string): string =>
    type === 'info'
        ? 'bg-blue-500/10 border-blue-500'
        : type === 'announcement'
        ? 'bg-reviactyl/10 border-reviactyl'
        : type === 'danger'
        ? 'bg-danger/10 border-danger'
        : type === 'success'
        ? 'bg-success/10 border-success'
        : type === 'warning'
        ? 'bg-yellow-500/10 border-yellow-500'
        : '';

const getAlertIcon = (type: string) =>
    type === 'info' ? (
        <InformationCircleIcon className='h-5 w-5 font-bold !text-blue-500' />
    ) : type === 'announcement' ? (
        <BellIcon className='h-5 w-5 font-bold !text-reviactyl' />
    ) : type === 'danger' ? (
        <InboxInIcon className='h-5 w-5 font-bold !text-danger/50' />
    ) : type === 'success' ? (
        <CheckIcon className='h-5 w-5 font-bold !text-success/50' />
    ) : type === 'warning' ? (
        <ExclamationIcon className='h-5 w-5 font-bold !text-yellow-500' />
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
