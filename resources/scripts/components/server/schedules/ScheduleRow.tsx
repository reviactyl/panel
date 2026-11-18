import { Schedule } from '@/api/server/schedules/getServerSchedules';
import { FaCalendarDays } from 'react-icons/fa6';
import { ServerContext } from '@/state/server';
import { formatInTimezone } from '@/lib/timezones';
import ScheduleCronRow from '@/components/server/schedules/ScheduleCronRow';

export default ({ schedule }: { schedule: Schedule }) => {
    const timezone = ServerContext.useStoreState(
        (state) => state.server.data!.timezone ?? state.server.data!.defaultTimezone,
    );
    const status = schedule.isProcessing ? 'Processing' : schedule.isActive ? 'Active' : 'Inactive';

    return (
        <>
            <div className='hidden md:block'>
                <FaCalendarDays className={'inline-block w-[1.25em]'} />
            </div>
            <div className='min-w-0 flex-1 md:ml-4'>
                <p className='wrap-anywhere'>{schedule.name}</p>
                <p className='text-xs text-gray-400'>
                    Last run at: {schedule.lastRunAt ? formatInTimezone(schedule.lastRunAt, timezone) : 'never'}
                </p>
            </div>
            <div>
                <p
                    className={`rounded-ui px-2 py-px text-xs ml-4 uppercase sm:hidden ${
                        schedule.isProcessing
                            ? 'bg-gray-600/50 text-gray-100'
                            : schedule.isActive
                              ? 'bg-success/20 text-success-text'
                              : 'bg-danger/20 text-danger-text'
                    }`}
                >
                    {status}
                </p>
            </div>
            <ScheduleCronRow
                cron={schedule.cron}
                className='mx-auto sm:mx-8 w-full sm:w-auto sm:flex-[2] mt-4 sm:mt-0'
            />
            <div>
                <p
                    className={`py-1 px-3 rounded-ui text-xs uppercase text-gray-100 hidden sm:block ${
                        schedule.isActive && !schedule.isProcessing ? 'bg-success/15' : 'bg-gray-600/50'
                    }`}
                >
                    {status}
                </p>
            </div>
        </>
    );
};
