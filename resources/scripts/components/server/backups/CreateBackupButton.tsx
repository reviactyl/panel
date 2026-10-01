import { useEffect, useState } from 'react';
import Modal, { RequiredModalProps } from '@/reviactyl/elements/Modal';
import { Field as FormikField, Form, Formik, FormikHelpers, useFormikContext } from 'formik';
import { boolean, object, string } from 'yup';
import Field from '@/reviactyl/elements/Field';
import FormikFieldWrapper from '@/reviactyl/elements/FormikFieldWrapper';
import useFlash from '@/plugins/useFlash';
import createServerBackup from '@/api/server/backups/createServerBackup';
import FlashMessageRender from '@/components/FlashMessageRender';
import Button from '@/reviactyl/elements/Button';
import { Textarea } from '@/reviactyl/elements/Input';
import getServerBackups from '@/api/swr/getServerBackups';
import { ServerContext } from '@/state/server';
import FormikSwitch from '@/reviactyl/elements/FormikSwitch';
import Can from '@/reviactyl/elements/Can';
import { useTranslation } from 'react-i18next';
import { useStoreState } from 'easy-peasy';
import { FaChevronDown } from 'react-icons/fa6';

interface Values {
    name: string;
    ignored: string;
    isLocked: boolean;
    format: 'tar.gz' | 'zip';
}

const ModalContent = ({ ...props }: RequiredModalProps) => {
    const { t } = useTranslation('server/backups');
    const { isSubmitting } = useFormikContext<Values>();

    return (
        <Modal {...props} showSpinnerOverlay={isSubmitting}>
            <Form>
                <FlashMessageRender byKey={'backups:create'} className='mb-4' />
                <h2 className='text-2xl mb-6'>{t('create-backup')}</h2>
                <Field name={'name'} label={t('backup-name')} description={t('name-description')} />
                <div className='mt-6'>
                    <label htmlFor='backup-format' className='block mb-2 text-sm'>
                        {t('format')}
                    </label>
                    <div className='relative'>
                        <FormikField
                            as='select'
                            id='backup-format'
                            name='format'
                            className='w-full appearance-none rounded-ui border border-gray-700 bg-gray-900 bg-none p-2 pr-10 text-gray-100'
                        >
                            <option value='tar.gz'>tar.gz</option>
                            <option value='zip'>zip</option>
                        </FormikField>
                        <FaChevronDown
                            className='pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-100'
                            aria-hidden
                        />
                    </div>
                </div>
                <div className='mt-6'>
                    <FormikFieldWrapper name={'ignored'} label={t('ignored')} description={t('ignored-description')}>
                        <FormikField as={Textarea} name={'ignored'} rows={6} />
                    </FormikFieldWrapper>
                </div>
                <Can action={'backup.delete'}>
                    <div className='mt-6 bg-gray-900 border border-gray-900 shadow-inner p-4 rounded'>
                        <FormikSwitch name={'isLocked'} label={'Locked'} description={t('locked-description')} />
                    </div>
                </Can>
                <div className='flex justify-end mt-6'>
                    <Button type={'submit'} disabled={isSubmitting}>
                        {t('start-backup')}
                    </Button>
                </div>
            </Form>
        </Modal>
    );
};

export default () => {
    const { t } = useTranslation('server/backups');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const archiveFormat = useStoreState((state) => state.user.data!.archiveFormat);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [visible, setVisible] = useState(false);
    const { mutate } = getServerBackups();

    useEffect(() => {
        clearFlashes('backups:create');
    }, [visible]);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('backups:create');
        createServerBackup(uuid, values)
            .then((backup) => {
                mutate(
                    (data) =>
                        data && {
                            ...data,
                            items: data.items.concat(backup),
                            backupCount: data.backupCount + 1,
                        },
                    false,
                );
                setVisible(false);
            })
            .catch((error) => {
                clearAndAddHttpError({ key: 'backups:create', error });
                setSubmitting(false);
            });
    };

    return (
        <>
            {visible && (
                <Formik
                    onSubmit={submit}
                    initialValues={{ name: '', ignored: '', isLocked: false, format: archiveFormat }}
                    validationSchema={object().shape({
                        name: string().max(191),
                        ignored: string(),
                        isLocked: boolean(),
                        format: string().oneOf(['tar.gz', 'zip']).required(),
                    })}
                >
                    <ModalContent appear visible={visible} onDismissed={() => setVisible(false)} />
                </Formik>
            )}
            <Button className='w-full sm:w-auto' onClick={() => setVisible(true)}>
                {t('create-backup')}
            </Button>
        </>
    );
};
