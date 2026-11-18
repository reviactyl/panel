import { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { StoreProvider } from 'easy-peasy';
import type { Server } from '@/api/server/getServer';
import type { Schedule } from '@/api/server/schedules/getServerSchedules';
import { ServerContext } from '@/state/server';
import { store } from '@/state';
import ScheduleRow from '@/components/server/schedules/ScheduleRow';
import ScheduleCronRow from '@/components/server/schedules/ScheduleCronRow';
import ScheduleCronBoxes from '@/components/server/schedules/ScheduleCronBoxes';
import TaskDetailsModal from '@/components/server/schedules/TaskDetailsModal';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import '@/assets/tailwind.css';

const cron = {
    minute: Array.from({ length: 30 }, (_, index) => index * 2).join(','),
    hour: '0,2,4,6,8,10,12,14,16,18,20,22',
    dayOfMonth: '1,3,5,7,9,11,13,15,17,19,21,23,25,27,29',
    month: '1,3,5,7,9,11',
    dayOfWeek: '0,1,2,3,4,5,6',
};
const schedule: Schedule = {
    id: 1,
    name: 'Schedule layout regression',
    cron,
    isActive: true,
    isProcessing: true,
    onlyWhenOnline: false,
    lastRunAt: null,
    nextRunAt: null,
    createdAt: new Date(),
    updatedAt: new Date(),
    tasks: [],
};
function Fixture() {
    const actions = ServerContext.useStoreActions((actions) => actions);
    const [ready, setReady] = useState(false);
    const [visible, setVisible] = useState(false);
    useEffect(() => {
        actions.server.setServer({ uuid: 'server-uuid', timezone: 'UTC', featureLimits: { backups: 1 } } as Server);
        actions.server.setPermissions(['*']);
        setReady(true);
    }, []);
    if (!ready) return null;
    return (
        <main className='p-4'>
            {[
                schedule,
                { ...schedule, name: 'Inactive schedule', isActive: false, isProcessing: false },
                { ...schedule, name: 'Active schedule', isProcessing: false },
                { ...schedule, name: 'Inactive processing schedule', isActive: false },
            ].map((item) => (
                <GreyRowBox key={item.name} className='flex-wrap mb-2' data-testid={item.name}>
                    <ScheduleRow schedule={item} />
                </GreyRowBox>
            ))}
            <section data-testid='cron-detail'>
                <div className='sm:hidden'>
                    <ScheduleCronRow cron={cron} />
                </div>
                <ScheduleCronBoxes cron={cron} />
            </section>
            <button onClick={() => setVisible(true)}>Open task form</button>
            <TaskDetailsModal schedule={schedule} visible={visible} onModalDismissed={() => setVisible(false)} />
        </main>
    );
}
createRoot(document.getElementById('root')!).render(
    <StoreProvider store={store}>
        <ServerContext.Provider>
            <Fixture />
        </ServerContext.Provider>
    </StoreProvider>,
);
