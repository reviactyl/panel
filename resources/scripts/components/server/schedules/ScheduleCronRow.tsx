import { Schedule } from '@/api/server/schedules/getServerSchedules';
import classNames from 'classnames';
import { useTranslation } from 'react-i18next';

interface Props {
    cron: Schedule['cron'];
    className?: string;
}

const ScheduleCronRow = ({ cron, className }: Props) => {
    const { t } = useTranslation('server/schedules');

    return (
        <div className={classNames('flex', className)}>
            <div className={'w-1/5 sm:w-auto text-center'}>
                <p className={'font-medium'}>{cron.minute}</p>
                <p className={'text-2xs text-gray-600 uppercase'}>{t('cron.minute')}</p>
            </div>
            <div className={'w-1/5 sm:w-auto text-center ml-4'}>
                <p className={'font-medium'}>{cron.hour}</p>
                <p className={'text-2xs text-gray-600 uppercase'}>{t('cron.hour')}</p>
            </div>
            <div className={'w-1/5 sm:w-auto text-center ml-4'}>
                <p className={'font-medium'}>{cron.dayOfMonth}</p>
                <p className={'text-2xs text-gray-600 uppercase'}>{t('cron.day-month')}</p>
            </div>
            <div className={'w-1/5 sm:w-auto text-center ml-4'}>
                <p className={'font-medium'}>{cron.month}</p>
                <p className={'text-2xs text-gray-600 uppercase'}>{t('cron.month')}</p>
            </div>
            <div className={'w-1/5 sm:w-auto text-center ml-4'}>
                <p className={'font-medium'}>{cron.dayOfWeek}</p>
                <p className={'text-2xs text-gray-600 uppercase'}>{t('cron.day-week')}</p>
            </div>
        </div>
    );
};

export default ScheduleCronRow;

export default ScheduleCronRow;
