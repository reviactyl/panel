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
import tw from 'twin.macro';
import ConfirmationModal from '@/reviactyl/elements/ConfirmationModal';
import Icon from '@/reviactyl/elements/Icon';
import { useTranslation } from 'react-i18next';

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

    const onConfirmDeletion = () => {
        setIsLoading(true);
        clearFlashes('schedules');
        deleteScheduleTask(uuid, schedule.id, task.id)
            .then(() =>
                appendSchedule({
                    ...schedule,
                    tasks: schedule.tasks.filter((t) => t.id !== task.id),
                })
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
        <div css={tw`sm:flex items-center p-3 sm:p-6 border-b border-gray-900`}>
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
            <div css={tw`flex-none sm:flex-1 w-full sm:w-auto overflow-x-auto`}>
                <p css={tw`md:ml-6 text-gray-200 uppercase text-sm`}>{title}</p>
                {task.payload && (
                    <div css={tw`md:ml-6 mt-2`}>
                        {task.action === 'backup' && (
                            <p css={tw`text-xs uppercase text-gray-400 mb-1`}>{t('ignoring-files-folders')}</p>
                        )}
                        <div
                            css={tw`font-mono bg-gray-900 rounded py-1 px-2 text-sm w-auto inline-block whitespace-pre-wrap break-all`}
                        >
                            {task.payload}
                        </div>
                    </div>
                )}
            </div>
            <div css={tw`mt-3 sm:mt-0 flex items-center w-full sm:w-auto`}>
                {task.continueOnFailure && (
                    <div css={tw`mr-6`}>
                        <div css={tw`flex items-center px-2 py-1 bg-yellow-500 text-yellow-800 text-sm rounded-full`}>
                            <Icon icon={FaCircleArrowDown} css={tw`w-3 h-3 mr-2`} />
                            {t('continues-on-failure')}
                        </div>
                    </div>
                )}
                {task.sequenceId > 1 && task.timeOffset > 0 && (
                    <div css={tw`mr-6`}>
                        <div css={tw`flex items-center px-2 py-1 bg-gray-600 text-sm rounded-full`}>
                            <Icon icon={FaClock} css={tw`w-3 h-3 mr-2`} />
                            {t('time-offset-later', { time: task.timeOffset })}
                        </div>
                    </div>
                )}
                <Can action={'schedule.update'}>
                    <button
                        type={'button'}
                        aria-label={t('edit-scheduled-task')}
                        css={tw`block text-sm p-2 text-gray-600 hover:text-gray-100 transition-colors duration-150 mr-4 ml-auto sm:ml-0`}
                        onClick={() => setIsEditing(true)}
                    >
                        <FaPen />
                    </button>
                </Can>
                <Can action={'schedule.update'}>
                    <button
                        type={'button'}
                        aria-label={t('delete-scheduled-task')}
                        css={tw`block text-sm p-2 text-gray-600 hover:text-red-600 transition-colors duration-150`}
                        onClick={() => setVisible(true)}
                    >
                        <FaTrash />
                    </button>
                </Can>
            </div>
        </div>
    );
};
