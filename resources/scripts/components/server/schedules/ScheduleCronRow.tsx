import { Schedule } from '@/api/server/schedules/getServerSchedules';
import classNames from 'classnames';
import CronValue from './CronValue';

interface Props {
    cron: Schedule['cron'];
    className?: string;
}

const ScheduleCronRow = ({ cron, className }: Props) => (
    <div className={classNames('grid min-w-0 grid-cols-5 gap-2 sm:gap-4', className)}>
        <div className={'min-w-0 text-center'}>
            <p className={'wrap-anywhere font-medium'}>
                <CronValue value={cron.minute} />
            </p>
            <p className={'text-2xs text-muted uppercase'}>Minute</p>
        </div>
        <div className={'min-w-0 text-center'}>
            <p className={'wrap-anywhere font-medium'}>
                <CronValue value={cron.hour} />
            </p>
            <p className={'text-2xs text-muted uppercase'}>Hour</p>
        </div>
        <div className={'min-w-0 text-center'}>
            <p className={'wrap-anywhere font-medium'}>
                <CronValue value={cron.dayOfMonth} />
            </p>
            <p className={'text-2xs text-muted uppercase'}>Day (Month)</p>
        </div>
        <div className={'min-w-0 text-center'}>
            <p className={'wrap-anywhere font-medium'}>
                <CronValue value={cron.month} />
            </p>
            <p className={'text-2xs text-muted uppercase'}>Month</p>
        </div>
        <div className={'min-w-0 text-center'}>
            <p className={'wrap-anywhere font-medium'}>
                <CronValue value={cron.dayOfWeek} />
            </p>
            <p className={'text-2xs text-muted uppercase'}>Day (Week)</p>
        </div>
    </div>
);

export default ScheduleCronRow;
