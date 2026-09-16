import { useCallback, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import getServerSchedule from '@/api/server/schedules/getServerSchedule';
import Spinner from '@/reviactyl/elements/Spinner';
import FlashMessageRender from '@/components/FlashMessageRender';
import EditScheduleModal from '@/components/server/schedules/EditScheduleModal';
import NewTaskButton from '@/components/server/schedules/NewTaskButton';
import DeleteScheduleButton from '@/components/server/schedules/DeleteScheduleButton';
import Can from '@/reviactyl/elements/Can';
import useFlash from '@/plugins/useFlash';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/reviactyl/elements/ServerContentBlock';
import { Button } from '@/reviactyl/components/button/index';
import ScheduleTaskRow from '@/components/server/schedules/ScheduleTaskRow';
import isEqual from 'react-fast-compare';
import { format } from 'date-fns';
import ScheduleCronRow from '@/components/server/schedules/ScheduleCronRow';
import RunScheduleButton from '@/components/server/schedules/RunScheduleButton';
import Card from '@/reviactyl/ui/Card';
import { useTranslation } from 'react-i18next';

const CronBox = ({ title, value }: { title: string; value: string }) => (
    <div className='mx-2 rounded-ui bg-gray-800 p-3'>
        <p className='text-sm text-gray-300'>{title}</p>
        <p className='text-xl font-medium text-gray-100'>{value}</p>
    </div>
);

const ActivePill = ({ active }: { active: boolean }) => (
    <span
        className={`rounded-ui px-2 py-px text-xs ml-4 uppercase ${
            active ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger'
        }`}
    >
        {active ? 'Active' : 'Inactive'}
    </span>
);

const ScheduleEditContainer = () => {
    const { t } = useTranslation('server/schedules');
    const { id: scheduleId } = useParams<'id'>();
    const navigate = useNavigate();

    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);

    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [isLoading, setIsLoading] = useState(true);
    const [showEditModal, setShowEditModal] = useState(false);

    const schedule = ServerContext.useStoreState(
        (st) => st.schedules.data.find((s) => s.id === Number(scheduleId)),
        isEqual,
    );
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);

    useEffect(() => {
        if (schedule?.id === Number(scheduleId)) {
            setIsLoading(false);
            return;
        }

        clearFlashes('schedules');
        getServerSchedule(uuid, Number(scheduleId))
            .then((schedule) => appendSchedule(schedule))
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ error, key: 'schedules' });
            })
            .then(() => setIsLoading(false));
    }, [scheduleId]);

    const toggleEditModal = useCallback(() => {
        setShowEditModal((s) => !s);
    }, []);

    return (
        <ServerContentBlock className='pt-1' title={'Schedules'}>
            <FlashMessageRender byKey='schedules' className='mb-4' />
            {!schedule || isLoading ? (
                <Spinner size={'large'} centered />
            ) : (
                <>
                    <div className='mb-4 rounded-ui bg-gray-900 p-3 sm:hidden'>
                        <ScheduleCronRow cron={schedule.cron} />
                    </div>
                    <Card className='!p-1'>
                        <div className='items-center rounded-t border-b-4 border-gray-800 p-3 sm:flex sm:p-6'>
                            <div className='flex-1'>
                                <h3 className='flex items-center text-2xl text-gray-100'>
                                    {schedule.name}
                                    {schedule.isProcessing ? (
                                        <span className='ml-4 flex items-center rounded-full bg-gray-700 px-2 py-px text-xs uppercase text-white'>
                                            <span className='mr-2 flex h-3 w-3'>
                                                <Spinner />
                                            </span>
                                            Processing
                                        </span>
                                    ) : (
                                        <ActivePill active={schedule.isActive} />
                                    )}
                                </h3>
                                <p className='mt-1 text-sm text-gray-200'>
                                    Last run at:&nbsp;
                                    {schedule.lastRunAt ? (
                                        format(schedule.lastRunAt, "MMM do 'at' h:mma")
                                    ) : (
                                        <span className='text-gray-300'>n/a</span>
                                    )}
                                    <span className='ml-4 border-l-4 border-gray-800 py-px pl-4'>
                                        Next run at:&nbsp;
                                        {schedule.nextRunAt ? (
                                            format(schedule.nextRunAt, "MMM do 'at' h:mma")
                                        ) : (
                                            <span className='text-gray-300'>n/a</span>
                                        )}
                                    </span>
                                </p>
                            </div>
                            <div className='mt-3 flex sm:mt-0 sm:block'>
                                <Can action={'schedule.update'}>
                                    <Button.Text className={'flex-1 mr-4'} onClick={toggleEditModal}>
                                        Edit
                                    </Button.Text>
                                    <NewTaskButton schedule={schedule} />
                                </Can>
                            </div>
                        </div>
                        <div className='mt-4 mb-4 hidden grid-cols-5 gap-4 sm:grid md:grid-cols-5'>
                            <CronBox title={t('cron.minute')} value={schedule.cron.minute} />
                            <CronBox title={t('cron.hour')} value={schedule.cron.hour} />
                            <CronBox title={t('cron.day-month')} value={schedule.cron.dayOfMonth} />
                            <CronBox title={t('cron.month')} value={schedule.cron.month} />
                            <CronBox title={t('cron.day-week')} value={schedule.cron.dayOfWeek} />
                        </div>
                        <div className='rounded-b bg-gray-900'>
                            {schedule.tasks.length > 0
                                ? schedule.tasks
                                      .sort((a, b) =>
                                          a.sequenceId === b.sequenceId ? 0 : a.sequenceId > b.sequenceId ? 1 : -1,
                                      )
                                      .map((task) => (
                                          <ScheduleTaskRow
                                              key={`${schedule.id}_${task.id}`}
                                              task={task}
                                              schedule={schedule}
                                          />
                                      ))
                                : null}
                        </div>
                    </Card>
                    <EditScheduleModal visible={showEditModal} schedule={schedule} onModalDismissed={toggleEditModal} />
                    <div className='mt-6 flex sm:justify-end'>
                        <Can action={'schedule.delete'}>
                            <DeleteScheduleButton
                                scheduleId={schedule.id}
                                onDeleted={() => navigate(`/server/${id}/schedules`)}
                            />
                        </Can>
                        {schedule.tasks.length > 0 && (
                            <Can action={'schedule.update'}>
                                <RunScheduleButton schedule={schedule} />
                            </Can>
                        )}
                    </div>
                </>
            )}
        </ServerContentBlock>
    );
};

export default ScheduleEditContainer;
