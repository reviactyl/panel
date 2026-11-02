import { useEffect } from 'react';
import { Form, Formik, FormikHelpers } from 'formik';
import { object, string, number } from 'yup';
import { Dialog } from '@/reviactyl/elements/dialog';
import Button from '@/reviactyl/elements/Button';
import Field from '@/reviactyl/elements/Field';
import createServerCategory from '@/api/account/createServerCategory';
import updateServerCategory from '@/api/account/updateServerCategory';
import { ServerCategory } from '@/api/account/getServerCategories';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import { useTranslation } from 'react-i18next';
import styles from './CategoryManagerModal.module.css';

interface Values {
    name: string;
    description: string;
    color: string;
    priority: number;
}
interface Props {
    folder?: ServerCategory;
    parent?: string;
    onDismissed: () => void;
    onSaved: () => Promise<unknown>;
}

export default ({ folder, parent, onDismissed, onSaved }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    useEffect(() => {
        clearFlashes('folder-form');
    }, [clearFlashes]);
    const submit = async (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('folder-form');
        try {
            if (folder) await updateServerCategory(folder.uuid, values);
            else
                await createServerCategory({
                    ...values,
                    parentUuid: parent && !['all', 'primary'].includes(parent) ? parent : null,
                });
        } catch (error) {
            clearAndAddHttpError({ key: 'folder-form', error });
            setSubmitting(false);
            return;
        }
        setSubmitting(false);
        onDismissed();
        try {
            await onSaved();
        } catch (error) {
            clearAndAddHttpError({ key: 'dashboard', error });
        }
    };
    return (
        <Formik
            initialValues={{
                name: folder?.name || '',
                description: folder?.description || '',
                color: folder?.color || '#3b82f6',
                priority: folder?.priority ?? 0,
            }}
            validationSchema={object({
                name: string().required().max(191),
                description: string().nullable().max(255),
                color: string().matches(/^#([a-f0-9]{6}|[a-f0-9]{3})$/i, t('categories.color-invalid')),
                priority: number().required().integer().min(-2147483648).max(2147483647),
            })}
            onSubmit={submit}
        >
            {({ isSubmitting }) => (
                <Dialog
                    open
                    hideCloseIcon
                    preventExternalClose={isSubmitting}
                    onClose={() => {
                        if (!isSubmitting) onDismissed();
                    }}
                    title={t(folder ? 'folders.edit' : 'folders.create')}
                >
                    <FlashMessageRender byKey='folder-form' />
                    <Form id='folder-form' className='flex flex-col gap-4'>
                        <Field id='folder-name' name='name' label={t('folders.name')} />
                        <Field
                            id='folder-color'
                            name='color'
                            type='color'
                            label={t('folders.color')}
                            className={styles.folderColor}
                            style={{ height: '42px' }}
                        />
                        <Field
                            id='folder-priority'
                            name='priority'
                            type='number'
                            min={-2147483648}
                            max={2147483647}
                            step={1}
                            label={t('folders.priority')}
                        />
                        <Field id='folder-description' name='description' label={t('categories.description')} />
                    </Form>
                    <Dialog.Footer>
                        <Button type='button' isSecondary disabled={isSubmitting} onClick={onDismissed}>
                            {t('folders.close')}
                        </Button>
                        <Button type='submit' form='folder-form' isLoading={isSubmitting} disabled={isSubmitting}>
                            {t(folder ? 'folders.edit' : 'folders.create')}
                        </Button>
                    </Dialog.Footer>
                </Dialog>
            )}
        </Formik>
    );
};
