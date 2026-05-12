import { Schedule } from '@/api/server/schedules/getServerSchedules';
import { FaCalendarDays } from 'react-icons/fa6';
import { format } from 'date-fns';
import tw from 'twin.macro';
import ScheduleCronRow from '@/components/server/schedules/ScheduleCronRow';

export default ({ schedule }: { schedule: Schedule }) => (
    <>
        <div css={tw`hidden md:block`}>
            <FaCalendarDays className={'inline-block w-[1.25em]'} />
        </div>
        <div css={tw`flex-1 md:ml-4`}>
            <p>{schedule.name}</p>
            <p css={tw`text-xs text-gray-400`}>
                Last run at: {schedule.lastRunAt ? format(schedule.lastRunAt, "MMM do 'at' h:mma") : 'never'}
            </p>
        </div>
        <div>
            <p
                className={`rounded-ui px-2 py-px text-xs ml-4 uppercase sm:hidden ${
                    schedule.isActive ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger'
                }`}
            >
                {schedule.isActive ? 'Active' : 'Inactive'}
            </p>
        </div>
        <ScheduleCronRow cron={schedule.cron} css={tw`mx-auto sm:mx-8 w-full sm:w-auto mt-4 sm:mt-0`} />
        <div>
            <p
                className={`py-1 px-3 rounded-ui text-xs uppercase text-white hidden sm:block ${
                    schedule.isActive && !schedule.isProcessing ? 'bg-success/50' : 'bg-gray-600/50'
                }`}
            >
                {schedule.isProcessing ? 'Processing' : schedule.isActive ? 'Active' : 'Inactive'}
            </p>
        </div>
    </>
);
