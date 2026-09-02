import { useState } from 'react';
import { Schedule } from '@/api/server/schedules/getServerSchedules';
import TaskDetailsModal from '@/components/server/schedules/TaskDetailsModal';
import { Button } from '@/reviactyl/components/button/index';
import { usePermissions } from '@/plugins/usePermissions';
import { taskActionPermissions } from '@/components/server/schedules/taskPermissions';

interface Props {
    schedule: Schedule;
}

export default ({ schedule }: Props) => {
    const [visible, setVisible] = useState(false);
    const canCreateTask = usePermissions(taskActionPermissions).some(Boolean);

    if (!canCreateTask) return null;

    return (
        <>
            <TaskDetailsModal schedule={schedule} visible={visible} onModalDismissed={() => setVisible(false)} />
            <Button onClick={() => setVisible(true)} className={'flex-1'}>
                New Task
            </Button>
        </>
    );
};
