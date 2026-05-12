import { useState } from 'react';
import deleteSchedule from '@/api/server/schedules/deleteSchedule';
import { ServerContext } from '@/state/server';
import { Actions, useStoreActions } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import { Button } from '@/reviactyl/elements/button/index';
import { Dialog } from '@/reviactyl/elements/dialog';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import { useTranslation } from 'react-i18next';

interface Props {
    scheduleId: number;
    onDeleted: () => void;
}

export default ({ scheduleId, onDeleted }: Props) => {
    const { t } = useTranslation('server/schedules');
    const [visible, setVisible] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const onDelete = () => {
        setIsLoading(true);
        clearFlashes('schedules');
        deleteSchedule(uuid, scheduleId)
            .then(() => {
                setIsLoading(false);
                onDeleted();
            })
            .catch((error) => {
                console.error(error);

                addError({ key: 'schedules', message: httpErrorToHuman(error) });
                setIsLoading(false);
                setVisible(false);
            });
    };

    return (
        <>
            <Dialog.Confirm
                open={visible}
                onClose={() => setVisible(false)}
                title={t('delete-schedule-title')}
                confirm={t('delete-confirm')}
                onConfirmed={onDelete}
            >
                <SpinnerOverlay visible={isLoading} />
                {t('delete-schedule-message')}
            </Dialog.Confirm>
            <Button.Danger
                variant={Button.Variants.Secondary}
                className={'flex-1 sm:flex-none mr-4 border-transparent'}
                onClick={() => setVisible(true)}
            >
                {t('delete-confirm')}
            </Button.Danger>
        </>
    );
};
