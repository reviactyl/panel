import { useContext, useEffect } from 'react';
import { Schedule, Task } from '@/api/server/schedules/getServerSchedules';
import { Field as FormikField, Form, Formik, FormikHelpers, useField } from 'formik';
import { ServerContext } from '@/state/server';
import createOrUpdateScheduleTask from '@/api/server/schedules/createOrUpdateScheduleTask';
import { httpErrorToHuman } from '@/api/http';
import Field from '@/reviactyl/elements/Field';
import FlashMessageRender from '@/components/FlashMessageRender';
import { boolean, number, object, string } from 'yup';
import useFlash from '@/plugins/useFlash';
import FormikFieldWrapper from '@/reviactyl/elements/FormikFieldWrapper';
import tw from 'twin.macro';
import Label from '@/reviactyl/elements/Label';
import { Textarea } from '@/reviactyl/elements/Input';
import { Button } from '@/reviactyl/elements/button/index';
import Select from '@/reviactyl/elements/Select';
import ModalContext from '@/context/ModalContext';
import asModal from '@/hoc/asModal';
import FormikSwitch from '@/reviactyl/elements/FormikSwitch';

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

const schema = object().shape({
    action: string().required().oneOf(['command', 'power', 'backup']),
    payload: string().when('action', {
        is: (v) => v !== 'backup',
        then: string().required('A task payload must be provided.'),
        otherwise: string(),
    }),
    continueOnFailure: boolean(),
    timeOffset: number()
        .typeError('The time offset must be a valid number between 0 and 900.')
        .required('A time offset value must be provided.')
        .min(0, 'The time offset must be at least 0 seconds.')
        .max(900, 'The time offset must be less than 900 seconds.'),
});

const ActionListener = () => {
    const [{ value }, { initialValue: initialAction }] = useField<string>('action');
    const [, { initialValue: initialPayload }, { setValue, setTouched }] = useField<string>('payload');

    useEffect(() => {
        if (value !== initialAction) {
            setValue(value === 'power' ? 'start' : '');
            setTouched(false);
        } else {
            setValue(initialPayload || '');
            setTouched(false);
        }
    }, [value]);

    return null;
};

import { useTranslation } from 'react-i18next';
const TaskDetailsModal = ({ schedule, task }: Props) => {
    const { dismiss } = useContext(ModalContext);
    const { clearFlashes, addError } = useFlash();
    const { t } = useTranslation('server/schedules');

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);
    const backupLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.backups);
            then: string().required(t('task-payload-required')),
    useEffect(() => {
        return () => {
            clearFlashes('schedule:task');
        };
            .typeError(t('time-offset-type'))
            .required(t('time-offset-required'))
            .min(0, t('time-offset-min'))
            .max(900, t('time-offset-max')),
        if (backupLimit === 0 && values.action === 'backup') {
            setSubmitting(false);
            addError({
            message: t('backup-limit-zero'),
                key: 'schedule:task',
            });
        } else {
            createOrUpdateScheduleTask(uuid, schedule.id, task?.id, values)
        <h2 css={tw`text-2xl mb-6`}>{task ? t('edit-task') : t('create-task')}</h2>
                    let tasks = schedule.tasks.map((t) => (t.id === task.id ? task : t));
                    if (!schedule.tasks.find((t) => t.id === task.id)) {
                <Label>{t('action')}</Label>
                    }

                    appendSchedule({ ...schedule, tasks });
                        <option value={'command'}>{t('action-command')}</option>
                        <option value={'power'}>{t('action-power')}</option>
                        <option value={'backup'}>{t('action-backup')}</option>
                    console.error(error);
                    setSubmitting(false);
                    addError({ message: httpErrorToHuman(error), key: 'schedule:task' });
                });
        }
    };
                    label={t('time-offset')}
                    description={t('time-offset-description')}
            validationSchema={schema}
            initialValues={{
                action: task?.action || 'command',
                payload: task?.payload || '',
                timeOffset: task?.timeOffset.toString() || '0',
                continueOnFailure: task?.continueOnFailure || false,
                    <Label>{t('payload')}</Label>
        >
            {({ isSubmitting, values }) => (
                <Form css={tw`m-0`}>
                    <FlashMessageRender byKey={'schedule:task'} css={tw`mb-4`} />
                    <h2 css={tw`text-2xl mb-6`}>{task ? 'Edit Task' : 'Create Task'}</h2>
                    <div css={tw`flex`}>
                    <Label>{t('payload')}</Label>
                            <Label>Action</Label>
                            <ActionListener />
                            <option value={'start'}>{t('power-start')}</option>
                            <option value={'restart'}>{t('power-restart')}</option>
                            <option value={'stop'}>{t('power-stop')}</option>
                            <option value={'kill'}>{t('power-kill')}</option>
                                    <option value={'backup'}>Create backup</option>
                                </FormikField>
                            </FormikFieldWrapper>
                        </div>
                        <div css={tw`flex-1 ml-6`}>
                    <Label>{t('ignored-files')}</Label>
                                name={'timeOffset'}
                                label={'Time offset (in seconds)'}
                        description={t('ignored-files-description')}
                            />
                        </div>
                    </div>
                    <div css={tw`mt-6`}>
                        {values.action === 'command' ? (
                            <div>
                                <Label>Payload</Label>
                                <FormikFieldWrapper name={'payload'}>
                                    <FormikField as={Textarea} name={'payload'} rows={6} />
                description={t('continue-on-failure-description')}
                label={t('continue-on-failure')}
                        ) : values.action === 'power' ? (
                            <div>
                                <Label>Payload</Label>
                                <FormikFieldWrapper name={'payload'}>
                {task ? t('save-changes') : t('create-task')}
                                        <option value={'start'}>Start the server</option>
                                        <option value={'restart'}>Restart the server</option>
                                        <option value={'stop'}>Stop the server</option>
                                        <option value={'kill'}>Terminate the server</option>
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
                    <div css={tw`mt-6 bg-gray-900 border border-gray-900 shadow-inner p-4 rounded`}>
                        <FormikSwitch
                            name={'continueOnFailure'}
                            description={'Future tasks will be run when this task fails.'}
                            label={'Continue on Failure'}
                        />
                    </div>
                    <div css={tw`flex justify-end mt-6`}>
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
