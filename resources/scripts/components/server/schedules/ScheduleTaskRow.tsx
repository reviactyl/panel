import { useState } from 'react';
import { Schedule, Task } from '@/api/server/schedules/getServerSchedules';
import { FaCircleArrowDown, FaClock, FaCode, FaFileZipper, FaPen, FaToggleOn, FaTrash } from 'react-icons/fa6';
import { IconType } from 'react-icons';
import deleteScheduleTask from '@/api/server/schedules/deleteScheduleTask';
import { httpErrorToHuman } from '@/api/http';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import TaskDetailsModal from '@/components/server/schedules/TaskDetailsModal';
import Can from '@/reviactyl/elements/Can';
import useFlash from '@/plugins/useFlash';
import { ServerContext } from '@/state/server';
import ConfirmationModal from '@/reviactyl/elements/ConfirmationModal';
import Icon from '@/reviactyl/elements/Icon';
import { useTranslation } from 'react-i18next';
import { usePermissions } from '@/plugins/usePermissions';
import { taskActionPermissions } from '@/components/server/schedules/taskPermissions';

interface Props {
    schedule: Schedule;
    task: Task;
}

const getActionDetails = (action: string): [string, IconType] => {
    switch (action) {
        case 'command':
            return ['Send Command', FaCode];
        case 'power':
            return ['Send Power Action', FaToggleOn];
        case 'backup':
            return ['Create Backup', FaFileZipper];
        default:
            return ['Unknown Action', FaCode];
    }
};

export default ({ schedule, task }: Props) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, addError } = useFlash();
    const [visible, setVisible] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);
    const { t } = useTranslation('server/schedules');
    const canEditTask = usePermissions(taskActionPermissions).some(Boolean);

    const onConfirmDeletion = () => {
        setIsLoading(true);
        clearFlashes('schedules');
        deleteScheduleTask(uuid, schedule.id, task.id)
            .then(() =>
                appendSchedule({
                    ...schedule,
                    tasks: schedule.tasks.filter((t) => t.id !== task.id),
                }),
            )
            .catch((error) => {
                console.error(error);
                setIsLoading(false);
                addError({ message: httpErrorToHuman(error), key: 'schedules' });
            });
    };

    const [title, icon] = getActionDetails(task.action);
    const ActionIcon = icon;

    return (
        <div className='mt-2 items-center rounded-ui border border-gray-800 p-3 sm:flex sm:p-6'>
            <SpinnerOverlay visible={isLoading} fixed size={'large'} />
            <TaskDetailsModal
                schedule={schedule}
                task={task}
                visible={isEditing}
                onModalDismissed={() => setIsEditing(false)}
            />
            <ConfirmationModal
                title={t('confirm-task-deletion')}
                buttonText={t('delete-task')}
                onConfirmed={onConfirmDeletion}
                visible={visible}
                onModalDismissed={() => setVisible(false)}
            >
                {t('confirm-task-deletion-body')}
            </ConfirmationModal>
            <ActionIcon className={'text-lg text-white hidden md:block'} />
            <div className='w-full flex-none overflow-x-auto sm:w-auto sm:flex-1'>
                <p className='text-sm font-semibold uppercase text-gray-200 md:ml-6'>{title}</p>
                {task.payload && (
                    <div className='mt-2 md:ml-6'>
                        {task.action === 'backup' && (
                            <p className='mb-1 text-xs font-semibold uppercase text-gray-400'>
                                {t('ignoring-files-folders')}
                            </p>
                        )}
                        <div className='inline-block w-auto whitespace-pre-wrap break-all rounded bg-gray-800 px-2 py-1 font-mono text-sm'>
                            {task.payload}
                        </div>
                    </div>
                )}
            </div>
            <div className='mt-3 flex w-full items-center sm:mt-0 sm:w-auto'>
                {task.continueOnFailure && (
                    <div className='mr-6'>
                        <div className='flex items-center rounded-full bg-yellow-500 px-2 py-1 text-sm text-yellow-800'>
                            <Icon icon={FaCircleArrowDown} className='mr-2 h-3 w-3' />
                            {t('continues-on-failure')}
                        </div>
                    </div>
                )}
                {task.sequenceId > 1 && task.timeOffset > 0 && (
                    <div className='mr-6'>
                        <div className='flex items-center rounded-full bg-gray-600 px-2 py-1 text-sm'>
                            <Icon icon={FaClock} className='mr-2 h-3 w-3' />
                            {t('time-offset-later', { time: task.timeOffset })}
                        </div>
                    </div>
                )}
                {canEditTask && (
                    <Can action={'schedule.update'}>
                        <button
                            type={'button'}
                            aria-label={t('edit-scheduled-task')}
                            className='ml-auto mr-4 block p-2 text-sm text-gray-600 transition-colors duration-150 hover:text-gray-100 sm:ml-0'
                            onClick={() => setIsEditing(true)}
                        >
                            <FaPen />
                        </button>
                    </Can>
                )}
                <Can action={'schedule.update'}>
                    <button
                        type={'button'}
                        aria-label={t('delete-scheduled-task')}
                        className='block p-2 text-sm text-gray-600 transition-colors duration-150 hover:text-red-600'
                        onClick={() => setVisible(true)}
                    >
                        <FaTrash />
                    </button>
                </Can>
            </div>
        </div>
    );
};
