import type { Schedule } from '@/api/server/schedules/getServerSchedules';
import { useTranslation } from 'react-i18next';
import CronValue from './CronValue';

const CronBox = ({ title, value }: { title: string; value: string }) => (
    <div className='mx-2 min-w-0 rounded-ui bg-gray-800 p-3'>
        <p className='text-sm text-gray-300'>{title}</p>
        <p className='wrap-anywhere text-xl font-medium text-gray-100'>
            <CronValue value={value} />
        </p>
    </div>
);

export default function ScheduleCronBoxes({ cron }: { cron: Schedule['cron'] }) {
    const { t } = useTranslation('server/schedules');

    return (
        <div className='mt-4 mb-4 hidden grid-cols-5 gap-4 sm:grid'>
            <CronBox title={t('cron.minute')} value={cron.minute} />
            <CronBox title={t('cron.hour')} value={cron.hour} />
            <CronBox title={t('cron.day-month')} value={cron.dayOfMonth} />
            <CronBox title={t('cron.month')} value={cron.month} />
            <CronBox title={t('cron.day-week')} value={cron.dayOfWeek} />
        </div>
    );
}
