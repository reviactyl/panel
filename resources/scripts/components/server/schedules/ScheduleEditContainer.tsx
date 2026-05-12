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
import tw from 'twin.macro';
import { Button } from '@/reviactyl/elements/button/index';
import ScheduleTaskRow from '@/components/server/schedules/ScheduleTaskRow';
import isEqual from 'react-fast-compare';
import { format } from 'date-fns';
import ScheduleCronRow from '@/components/server/schedules/ScheduleCronRow';
import RunScheduleButton from '@/components/server/schedules/RunScheduleButton';
import Card from '@/reviactyl/ui/Card';
import { useTranslation } from 'react-i18next';

const CronBox = ({ title, value }: { title: string; value: string }) => (
    <div css={tw`bg-gray-800 rounded-ui p-3 mx-2`}>
        <p css={tw`text-gray-300 text-sm`}>{title}</p>
        <p css={tw`text-xl font-medium text-gray-100`}>{value}</p>
    </div>
);

const ActivePill = ({ active }: { active: boolean }) => (
    <span
        className={`rounded-ui px-2 py-px text-xs ml-4 uppercase ${active ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger'}`}
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
        isEqual
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
        <ServerContentBlock className="pt-1" title={'Schedules'}>
            <FlashMessageRender byKey={'schedules'} css={tw`mb-4`} />
            {!schedule || isLoading ? (
                <Spinner size={'large'} centered />
            ) : (
                <>
                    <ScheduleCronRow cron={schedule.cron} css={tw`sm:hidden bg-gray-900 rounded-ui mb-4 p-3`} />
                    <Card className="!p-1">
                        <div css={tw`sm:flex items-center p-3 sm:p-6 border-b-4 border-gray-800 rounded-t`}>
                            <div css={tw`flex-1`}>
                                <h3 css={tw`flex items-center text-gray-100 text-2xl`}>
                                    {schedule.name}
                                    {schedule.isProcessing ? (
                                        <span
                                            css={tw`flex items-center rounded-full px-2 py-px text-xs ml-4 uppercase bg-gray-700 text-white`}
                                        >
                                            <Spinner css={tw`w-3! h-3! mr-2`} />
                                            Processing
                                        </span>
                                    ) : (
                                        <ActivePill active={schedule.isActive} />
                                    )}
                                </h3>
                                <p css={tw`mt-1 text-sm text-gray-200`}>
                                    Last run at:&nbsp;
                                    {schedule.lastRunAt ? (
                                        format(schedule.lastRunAt, "MMM do 'at' h:mma")
                                    ) : (
                                        <span css={tw`text-gray-300`}>n/a</span>
                                    )}
                                    <span css={tw`ml-4 pl-4 border-l-4 border-gray-800 py-px`}>
                                        Next run at:&nbsp;
                                        {schedule.nextRunAt ? (
                                            format(schedule.nextRunAt, "MMM do 'at' h:mma")
                                        ) : (
                                            <span css={tw`text-gray-300`}>n/a</span>
                                        )}
                                    </span>
                                </p>
                            </div>
                            <div css={tw`flex sm:block mt-3 sm:mt-0`}>
                                <Can action={'schedule.update'}>
                                    <Button.Text className={'flex-1 mr-4'} onClick={toggleEditModal}>
                                        Edit
                                    </Button.Text>
                                    <NewTaskButton schedule={schedule} />
                                </Can>
                            </div>
                        </div>
                        <div css={tw`hidden sm:grid grid-cols-5 md:grid-cols-5 gap-4 mb-4 mt-4`}>
                            <CronBox title={t('cron.minute')} value={schedule.cron.minute} />
                            <CronBox title={t('cron.hour')} value={schedule.cron.hour} />
                            <CronBox title={t('cron.day-month')} value={schedule.cron.dayOfMonth} />
                            <CronBox title={t('cron.month')} value={schedule.cron.month} />
                            <CronBox title={t('cron.day-week')} value={schedule.cron.dayOfWeek} />
                        </div>
                        <div css={tw`bg-gray-900 rounded-b`}>
                            {schedule.tasks.length > 0
                                ? schedule.tasks
                                      .sort((a, b) =>
                                          a.sequenceId === b.sequenceId ? 0 : a.sequenceId > b.sequenceId ? 1 : -1
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
                    <div css={tw`mt-6 flex sm:justify-end`}>
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
