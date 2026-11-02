import { useEffect, useState } from 'react';
import { Form, Formik, FormikHelpers } from 'formik';
import { object, string, number } from 'yup';
import Modal from '@/reviactyl/elements/Modal';
import Button from '@/reviactyl/elements/Button';
import DeleteFolderModal from './DeleteFolderModal';
import Field from '@/reviactyl/elements/Field';
import createServerCategory from '@/api/account/createServerCategory';
import getServerCategories, { ServerCategory } from '@/api/account/getServerCategories';
import updateServerCategory from '@/api/account/updateServerCategory';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import { FaTrash, FaPen } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import { folderTree } from './folderTree';
import styles from './CategoryManagerModal.module.css';

interface Props {
    visible: boolean;
    folders?: boolean;
    currentFolder?: string;
    onDismissed: () => void;
    onCategoryChanged: () => void;
}
interface Values {
    name: string;
    description: string;
    color: string;
    priority: number;
}

export default ({ visible, onDismissed, onCategoryChanged, folders, currentFolder }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [categories, setCategories] = useState<ServerCategory[]>([]);
    const [deleting, setDeleting] = useState<ServerCategory | null>(null);
    const [editing, setEditing] = useState<ServerCategory | null>(null);
    const load = () =>
        getServerCategories()
            .then(setCategories)
            .catch((error) => clearAndAddHttpError({ key: 'category-modal', error }));
    useEffect(() => {
        if (visible) {
            clearFlashes('category-modal');
            setEditing(null);
            load();
        }
    }, [visible]);
    const submit = async (values: Values, { resetForm, setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('category-modal');
        try {
            if (editing) await updateServerCategory(editing.uuid, values);
            else
                await createServerCategory({
                    ...values,
                    ...(folders
                        ? {
                              parentUuid:
                                  currentFolder && !['all', 'primary'].includes(currentFolder) ? currentFolder : null,
                          }
                        : {}),
                });
            resetForm();
            setEditing(null);
            await load();
            onCategoryChanged();
        } catch (error) {
            clearAndAddHttpError({ key: 'category-modal', error });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <>
            <Modal visible={visible && !deleting} onDismissed={onDismissed} size='lg'>
                <h2 className='mb-5 text-2xl font-semibold'>
                    {t(folders ? (editing ? 'folders.edit' : 'folders.create') : 'categories.manage-title')}
                </h2>
                <FlashMessageRender byKey='category-modal' />
                <div className='grid gap-6 lg:grid-cols-2'>
                    <Formik
                        enableReinitialize
                        initialValues={{
                            name: editing?.name || '',
                            description: editing?.description || '',
                            color: editing?.color || '#3b82f6',
                            priority: editing?.priority ?? 0,
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
                            <Form className='flex flex-col gap-4'>
                                <Field
                                    id='category-name'
                                    name='name'
                                    label={t(folders ? 'folders.name' : 'categories.category-name')}
                                />
                                <Field
                                    id='category-color'
                                    name='color'
                                    type='color'
                                    label={t(folders ? 'folders.color' : 'categories.theme-color')}
                                    className={styles.folderColor}
                                    style={{ height: '42px' }}
                                />
                                <Field
                                    id='category-priority'
                                    name='priority'
                                    type='number'
                                    min={-2147483648}
                                    max={2147483647}
                                    step={1}
                                    label={t('folders.priority')}
                                />
                                <Field
                                    id='category-description'
                                    name='description'
                                    label={t('categories.description')}
                                />
                                <div className='flex flex-wrap gap-2'>
                                    <Button type='submit' disabled={isSubmitting}>
                                        {t(
                                            editing
                                                ? folders
                                                    ? 'folders.edit'
                                                    : 'categories.update-category'
                                                : folders
                                                  ? 'folders.create'
                                                  : 'categories.create-category-button',
                                        )}
                                    </Button>
                                    {editing && (
                                        <Button
                                            type='button'
                                            isSecondary
                                            disabled={isSubmitting}
                                            onClick={() => setEditing(null)}
                                        >
                                            {t('categories.discard-changes')}
                                        </Button>
                                    )}
                                </div>
                            </Form>
                        )}
                    </Formik>
                    <div className='max-h-96 space-y-2 overflow-y-auto'>
                        {folderTree(categories).map(({ folder, path }) => (
                            <div
                                key={folder.uuid}
                                className='flex items-center gap-2 rounded-ui border border-gray-700 p-3'
                            >
                                <span className='min-w-0 flex-1 break-words text-sm text-gray-100'>{path}</span>
                                <span className='text-sm tabular-nums text-gray-300'>{folder.priority ?? 0}</span>
                                <Button
                                    type='button'
                                    size='xsmall'
                                    isSecondary
                                    onClick={() => setEditing(folder)}
                                    aria-label={`${t('categories.edit')}: ${folder.name}`}
                                >
                                    <FaPen aria-hidden='true' />
                                </Button>
                                <Button
                                    type='button'
                                    size='xsmall'
                                    isSecondary
                                    onClick={() => setDeleting(folder)}
                                    aria-label={`${t('categories.delete')}: ${folder.name}`}
                                >
                                    <FaTrash aria-hidden='true' />
                                </Button>
                            </div>
                        ))}
                    </div>
                </div>
            </Modal>
            {deleting && (
                <DeleteFolderModal
                    folder={deleting}
                    folders={false}
                    onDismissed={() => setDeleting(null)}
                    onDeleted={async () => {
                        if (editing?.uuid === deleting.uuid) setEditing(null);
                        await load();
                        onCategoryChanged();
                    }}
                />
            )}
        </>
    );
};
