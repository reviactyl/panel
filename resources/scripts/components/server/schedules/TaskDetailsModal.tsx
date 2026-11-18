import { useContext, useEffect } from 'react';
import { Schedule, Task } from '@/api/server/schedules/getServerSchedules';
import { Field as FormikField, Form, Formik, FormikHelpers, useField } from 'formik';
import { ServerContext } from '@/state/server';
import createOrUpdateScheduleTask from '@/api/server/schedules/createOrUpdateScheduleTask';
import { httpErrorToHuman } from '@/api/http';
import Field from '@/reviactyl/elements/Field';
import FlashMessageRender from '@/components/FlashMessageRender';
import { taskSchema } from './taskValidation';
import useFlash from '@/plugins/useFlash';
import FormikFieldWrapper from '@/reviactyl/elements/FormikFieldWrapper';
import Label from '@/reviactyl/elements/Label';
import { Textarea } from '@/reviactyl/elements/Input';
import { Button } from '@/reviactyl/components/button/index';
import Select from '@/reviactyl/elements/Select';
import ModalContext from '@/context/ModalContext';
import asModal from '@/hoc/asModal';
import FormikSwitch from '@/reviactyl/elements/FormikSwitch';
import { usePermissions } from '@/plugins/usePermissions';
import { getTaskActionPermission, taskActionPermissions } from '@/components/server/schedules/taskPermissions';

interface Props {
    schedule: Schedule;
    // If a task is provided we can assume we're editing it. If not provided,
    // we are creating a new one.
    task?: Task;
}

interface Values {
    action: string;
    payload: string;
    timeOffset: string;
    continueOnFailure: boolean;
}

const ActionListener = ({ defaultPowerAction }: { defaultPowerAction: string }) => {
    const [{ value }, { initialValue: initialAction }] = useField<string>('action');
    const [, { initialValue: initialPayload }, { setValue, setTouched }] = useField<string>('payload');

    useEffect(() => {
        if (value !== initialAction) {
            setValue(value === 'power' ? defaultPowerAction : '');
            setTouched(false);
        } else {
            setValue(initialPayload || '');
            setTouched(false);
        }
    }, [value]);

    return null;
};

const TaskDetailsModal = ({ schedule, task }: Props) => {
    const { dismiss } = useContext(ModalContext);
    const { clearFlashes, addError } = useFlash();

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);
    const backupLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.backups);
    const [canSendCommand, canStart, canStop, canRestart, canCreateBackup] = usePermissions(taskActionPermissions);
    const defaultPowerAction = canStart ? 'start' : canRestart ? 'restart' : canStop ? 'stop' : '';
    const defaultAction = canSendCommand ? 'command' : defaultPowerAction ? 'power' : 'backup';
    const taskPermission = task ? getTaskActionPermission(task.action, task.payload) : null;
    const canEditExistingAction =
        taskPermission === 'control.console'
            ? canSendCommand
            : taskPermission === 'control.start'
              ? canStart
              : taskPermission === 'control.stop'
                ? canStop
                : taskPermission === 'control.restart'
                  ? canRestart
                  : taskPermission === 'backup.create'
                    ? canCreateBackup
                    : false;
    const initialAction = task && canEditExistingAction ? task.action : defaultAction;
    const initialPayload =
        task && canEditExistingAction ? task.payload : initialAction === 'power' ? defaultPowerAction : '';

    useEffect(() => {
        return () => {
            clearFlashes('schedule:task');
        };
    }, []);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('schedule:task');
        if (backupLimit === 0 && values.action === 'backup') {
            setSubmitting(false);
            addError({
                message: "A backup task cannot be created when the server's backup limit is set to 0.",
                key: 'schedule:task',
            });
        } else {
            createOrUpdateScheduleTask(uuid, schedule.id, task?.id, values)
                .then((task) => {
                    let tasks = schedule.tasks.map((t) => (t.id === task.id ? task : t));
                    if (!schedule.tasks.find((t) => t.id === task.id)) {
                        tasks = [...tasks, task];
                    }

                    appendSchedule({ ...schedule, tasks });
                    dismiss();
                })
                .catch((error) => {
                    console.error(error);
                    setSubmitting(false);
                    addError({ message: httpErrorToHuman(error), key: 'schedule:task' });
                });
        }
    };

    return (
        <Formik
            onSubmit={submit}
            validationSchema={taskSchema}
            initialValues={{
                action: initialAction,
                payload: initialPayload,
                timeOffset: task?.timeOffset.toString() || '0',
                continueOnFailure: task?.continueOnFailure || false,
            }}
        >
            {({ isSubmitting, values }) => (
                <Form className='m-0'>
                    <FlashMessageRender byKey={'schedule:task'} className='mb-4' />
                    <h2 className='text-2xl mb-6'>{task ? 'Edit Task' : 'Create Task'}</h2>
                    <div className='flex flex-col gap-4 sm:flex-row sm:gap-8'>
                        <div className='min-w-0 sm:w-1/3'>
                            <Label>Action</Label>
                            <ActionListener defaultPowerAction={defaultPowerAction} />
                            <FormikFieldWrapper name={'action'}>
                                <FormikField as={Select} name={'action'}>
                                    {canSendCommand && <option value={'command'}>Send command</option>}
                                    {defaultPowerAction && <option value={'power'}>Send power action</option>}
                                    {canCreateBackup && <option value={'backup'}>Create backup</option>}
                                </FormikField>
                            </FormikFieldWrapper>
                        </div>
                        <div className='min-w-0 flex-1'>
                            <Field
                                name={'timeOffset'}
                                label={'Time offset (in seconds)'}
                                description={
                                    'The amount of time to wait after the previous task executes before running this one. If this is the first task on a schedule this will not be applied.'
                                }
                            />
                        </div>
                    </div>
                    <div className='mt-6'>
                        {values.action === 'command' ? (
                            <div>
                                <Label>Payload</Label>
                                <FormikFieldWrapper name={'payload'}>
                                    <FormikField as={Textarea} name={'payload'} rows={6} />
                                </FormikFieldWrapper>
                            </div>
                        ) : values.action === 'power' ? (
                            <div>
                                <Label>Payload</Label>
                                <FormikFieldWrapper name={'payload'}>
                                    <FormikField as={Select} name={'payload'}>
                                        {canStart && <option value={'start'}>Start the server</option>}
                                        {canRestart && <option value={'restart'}>Restart the server</option>}
                                        {canStop && <option value={'stop'}>Stop the server</option>}
                                        {canStop && <option value={'kill'}>Terminate the server</option>}
                                    </FormikField>
                                </FormikFieldWrapper>
                            </div>
                        ) : (
                            <div>
                                <Label>Ignored Files</Label>
                                <FormikFieldWrapper
                                    name={'payload'}
                                    description={
                                        'Optional. Include the files and folders to be excluded in this backup. By default, the contents of your .pteroignore file will be used. If you have reached your backup limit, the oldest backup will be rotated.'
                                    }
                                >
                                    <FormikField as={Textarea} name={'payload'} rows={6} />
                                </FormikFieldWrapper>
                            </div>
                        )}
                    </div>
                    <div className='mt-6 bg-gray-800 border border-gray-700 shadow-inner p-4 rounded-ui'>
                        <FormikSwitch
                            name={'continueOnFailure'}
                            description={'Future tasks will be run when this task fails.'}
                            label={'Continue on Failure'}
                        />
                    </div>
                    <div className='flex justify-end mt-6'>
                        <Button type={'submit'} disabled={isSubmitting}>
                            {task ? 'Save Changes' : 'Create Task'}
                        </Button>
                    </div>
                </Form>
            )}
        </Formik>
    );
};

export default asModal<Props>()(TaskDetailsModal);
