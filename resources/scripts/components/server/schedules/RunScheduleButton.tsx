import { useEffect, useState } from 'react';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import { Button } from '@/reviactyl/components/button/index';
import triggerScheduleExecution from '@/api/server/schedules/triggerScheduleExecution';
import getServerSchedule from '@/api/server/schedules/getServerSchedule';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import { Schedule } from '@/api/server/schedules/getServerSchedules';

const RunScheduleButton = ({ schedule }: { schedule: Schedule }) => {
    const [loading, setLoading] = useState(false);
    const { clearFlashes, clearAndAddHttpError } = useFlash();

    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);

    useEffect(() => {
        if (!schedule.isProcessing) return;

        let cancelled = false;
        let timeout: ReturnType<typeof setTimeout>;
        const refresh = async () => {
            try {
                const updated = await getServerSchedule(id, schedule.id);
                if (cancelled) return;
                appendSchedule(updated);
                if (!updated.isProcessing) return;
            } catch (error) {
                if (cancelled) return;
                clearAndAddHttpError({ error, key: 'schedules' });
            }
            timeout = setTimeout(refresh, 2000);
        };

        timeout = setTimeout(refresh, 2000);
        return () => {
            cancelled = true;
            clearTimeout(timeout);
        };
    }, [id, schedule.id, schedule.isProcessing, appendSchedule, clearAndAddHttpError]);

    const onTriggerExecute = () => {
        clearFlashes('schedules');
        setLoading(true);
        triggerScheduleExecution(id, schedule.id)
            .then(() => getServerSchedule(id, schedule.id))
            .then((updated) => appendSchedule(updated))
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ error, key: 'schedules' });
            })
            .finally(() => setLoading(false));
    };

    return (
        <>
            <SpinnerOverlay visible={loading} size={'large'} />
            <Button
                variant={Button.Variants.Secondary}
                className={'flex-1 sm:flex-none'}
                disabled={loading || schedule.isProcessing}
                onClick={onTriggerExecute}
            >
                Run Now
            </Button>
        </>
    );
};

export default RunScheduleButton;
