import { useEffect, useRef, useState } from 'react';
import { Button } from '@/reviactyl/components/button/index';
import triggerScheduleExecution from '@/api/server/schedules/triggerScheduleExecution';
import getServerSchedule from '@/api/server/schedules/getServerSchedule';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import { Schedule } from '@/api/server/schedules/getServerSchedules';
import { httpErrorToHuman } from '@/api/http';

export type RunRequestState = 'idle' | 'pending' | 'failed';

const RunScheduleButton = ({
    schedule,
    onRequestStateChange,
}: {
    schedule: Schedule;
    onRequestStateChange: (state: RunRequestState) => void;
}) => {
    const mounted = useRef(true);
    const [loading, setLoading] = useState(false);
    const [awaitingStatus, setAwaitingStatus] = useState(false);
    const { addError, clearFlashes, clearAndAddHttpError } = useFlash();

    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);

    useEffect(() => {
        mounted.current = true;
        return () => {
            mounted.current = false;
            clearFlashes('schedule-status');
        };
    }, [id, schedule.id, clearFlashes]);

    useEffect(() => {
        if (loading || (!schedule.isProcessing && !awaitingStatus)) return;

        let cancelled = false;
        let retryDelay = 2000;
        let failed = false;
        let timeout: ReturnType<typeof setTimeout>;
        const refresh = async () => {
            try {
                const updated = await getServerSchedule(id, schedule.id);
                if (cancelled) return;
                appendSchedule(updated);
                setAwaitingStatus(false);
                onRequestStateChange('idle');
                clearFlashes('schedule-status');
                failed = false;
                retryDelay = 2000;
                if (!updated.isProcessing) return;
            } catch (error) {
                if (cancelled) return;
                if (!failed) {
                    clearFlashes('schedule-status');
                    addError({ message: httpErrorToHuman(error), key: 'schedule-status' });
                }
                failed = true;
                retryDelay = Math.min(retryDelay * 2, 30000);
            }
            timeout = setTimeout(refresh, retryDelay);
        };

        timeout = setTimeout(refresh, 2000);
        return () => {
            cancelled = true;
            clearTimeout(timeout);
        };
    }, [
        id,
        schedule.id,
        schedule.isProcessing,
        loading,
        awaitingStatus,
        appendSchedule,
        clearFlashes,
        addError,
        onRequestStateChange,
    ]);

    const onTriggerExecute = () => {
        clearFlashes('schedules');
        clearFlashes('schedule-status');
        setLoading(true);
        onRequestStateChange('pending');
        triggerScheduleExecution(id, schedule.id)
            .then(async () => {
                if (!mounted.current) return;
                setAwaitingStatus(true);
                try {
                    const updated = await getServerSchedule(id, schedule.id);
                    if (!mounted.current) return;
                    appendSchedule(updated);
                    setAwaitingStatus(false);
                    onRequestStateChange('idle');
                } catch (error) {
                    if (!mounted.current) return;
                    clearFlashes('schedule-status');
                    addError({ message: httpErrorToHuman(error), key: 'schedule-status' });
                }
            })
            .catch((error) => {
                if (!mounted.current) return;
                onRequestStateChange('failed');
                console.error(error);
                clearAndAddHttpError({ error, key: 'schedules' });
            })
            .finally(() => {
                if (mounted.current) setLoading(false);
            });
    };

    return (
        <Button
            variant={Button.Variants.Secondary}
            className={'flex-1 sm:flex-none'}
            disabled={loading || awaitingStatus || schedule.isProcessing}
            onClick={onTriggerExecute}
        >
            Run Now
        </Button>
    );
};

export default RunScheduleButton;
