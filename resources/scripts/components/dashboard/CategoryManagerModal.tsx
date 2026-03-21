import { useEffect, useState } from 'react';
import { Form, Formik, FormikHelpers } from 'formik';
import { object, string } from 'yup';
import Modal from '@/components/elements/Modal';
import Button from '@/components/elements/Button';
import Field from '@/components/elements/Field';
import createServerCategory from '@/api/account/createServerCategory';
import getServerCategories, { ServerCategory } from '@/api/account/getServerCategories';
import deleteServerCategory from '@/api/account/deleteServerCategory';
import updateServerCategory from '@/api/account/updateServerCategory';
import reorderServerCategories from '@/api/account/reorderServerCategories';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import styled from 'styled-components';
import { FaTrash, FaPen, FaPlus, FaLayerGroup, FaArrowDownWideShort, FaBars } from 'react-icons/fa6';
import { DragDropContext, Droppable, Draggable, DropResult } from '@hello-pangea/dnd';
import { useTranslation } from 'react-i18next';
import Title from '@/reviactyl/ui/Title';
import Card from '@/reviactyl/ui/Card';

interface Props {
    visible: boolean;
    onDismissed: () => void;
    onCategoryChanged: () => void;
}

interface Values {
    name: string;
    description: string;
    color: string;
}

const ResponsiveLayout = styled.div`
    display: flex;
    flex-direction: column;
    gap: 2rem;

    @media (min-width: 1024px) {
        flex-direction: row;
    }
`;

const Column = styled.div`
    flex: 1 1 0%;
    min-width: 0;
`;

const DragItem = styled.div<{ isDragging?: boolean }>`
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 0.85rem;
    margin-bottom: 0.75rem;
    transition: background-color 0.2s ease, border-color 0.2s ease;
    box-shadow: ${(props) => (props.isDragging ? '0 10px 15px -3px rgba(0, 0, 0, 0.4)' : 'none')};
    cursor: grab;

    @media (min-width: 640px) {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    &:active {
        cursor: grabbing;
    }
`;

export default ({ visible, onDismissed, onCategoryChanged }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [categories, setCategories] = useState<ServerCategory[]>([]);
    const [editingCategory, setEditingCategory] = useState<ServerCategory | null>(null);

    const loadCategories = () => {
        getServerCategories()
            .then((data) => setCategories(data))
            .catch((error: any) => clearAndAddHttpError({ key: 'category-modal', error }));
    };

    useEffect(() => {
        if (visible) {
            clearFlashes('category-modal');
            loadCategories();
        }
    }, [visible]);

    const submit = (values: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearFlashes('category-modal');
        const action = editingCategory
            ? updateServerCategory(editingCategory.uuid, values)
            : createServerCategory(values);

        action
            .then(() => {
                resetForm();
                setEditingCategory(null);
                loadCategories();
                onCategoryChanged();
            })
            .catch((error: any) => {
                clearAndAddHttpError({ key: 'category-modal', error });
                setSubmitting(false);
            });
    };

    const handleDelete = (uuid: string) => {
        if (!confirm(t('categories.delete-confirm'))) return;

        deleteServerCategory(uuid)
            .then(() => {
                loadCategories();
                onCategoryChanged();
            })
            .catch((error: any) => clearAndAddHttpError({ key: 'category-modal', error }));
    };

    const onDragEnd = (result: DropResult) => {
        if (!result.destination) return;

        const newCategories = [...categories];
        const [reorderedItem] = newCategories.splice(result.source.index, 1);
        if (!reorderedItem) return;
        newCategories.splice(result.destination.index, 0, reorderedItem);

        setCategories(newCategories);
        reorderServerCategories(newCategories.map((c) => c.uuid))
            .then(() => onCategoryChanged())
            .catch((error: any) => clearAndAddHttpError({ key: 'category-modal', error }));
    };

    return (
        <Modal visible={visible} onDismissed={onDismissed} dismissable={true} size={'lg'} noScroll={true}>
            <div className='mb-6 border-b border-gray-600 pb-4'>
                <Title className='text-2xl font-semibold text-gray-100'>{t('categories.manage-title')}</Title>
            </div>

            <div style={{ marginBottom: '1.5rem' }}>
                <FlashMessageRender byKey={'category-modal'} />
            </div>

            <ResponsiveLayout>
                {/* LEFT SECTION: CREATE/EDIT */}
                <Column>
                    <div className='flex items-center gap-2 mb-5'>
                        <div className='bg-blue-500/10 p-2 rounded-ui'>
                            {editingCategory ? (
                                <FaPen className='text-blue-400' />
                            ) : (
                                <FaPlus className='text-blue-400' />
                            )}
                        </div>
                        <h3 className='text-lg font-semibold text-gray-200'>
                            {editingCategory ? t('categories.modify-category') : t('categories.create-category')}
                        </h3>
                    </div>

                    <Card className='bg-[#1e293b] border border-[#334155] rounded-2xl p-6 shadow-sm'>
                        <Formik
                            onSubmit={submit}
                            initialValues={{
                                name: editingCategory?.name || '',
                                description: editingCategory?.description || '',
                                color: editingCategory?.color || '#3b82f6',
                            }}
                            validationSchema={object().shape({
                                name: string().required().max(191),
                                description: string().max(255).nullable(),
                                color: string()
                                    .matches(/^#([a-f0-9]{6}|[a-f0-9]{3})$/i, t('categories.color-invalid'))
                                    .max(7),
                            })}
                            enableReinitialize
                        >
                            {({ isSubmitting, values }) => (
                                <Form>
                                    <div className='flex flex-col gap-5'>
                                        <Field
                                            name={'name'}
                                            label={t('categories.category-name')}
                                            placeholder={t('categories.name-placeholder')}
                                        />

                                        <div className='flex items-end gap-3'>
                                            <div className='flex-1'>
                                                <Field
                                                    name={'color'}
                                                    label={t('categories.theme-color')}
                                                    type={'color'}
                                                    style={{ height: '42px', padding: '0.2rem' }}
                                                />
                                            </div>
                                            <div className='flex-none pb-2 text-xs text-gray-400 flex items-center gap-1.5'>
                                                <div
                                                    style={{
                                                        backgroundColor: values.color || '#3b82f6',
                                                    }}
                                                    className='w-3 h-3 rounded-full transition-colors duration-200'
                                                />
                                                {t('categories.preview')}
                                            </div>
                                        </div>

                                        <Field
                                            name={'description'}
                                            label={t('categories.description')}
                                            placeholder={t('categories.description-placeholder')}
                                        />

                                        <div className='mt-2 flex flex-col gap-3'>
                                            <Button
                                                type={'submit'}
                                                disabled={isSubmitting}
                                                isLoading={isSubmitting}
                                                className='w-full'
                                            >
                                                {editingCategory
                                                    ? t('categories.update-category')
                                                    : t('categories.create-category-button')}
                                            </Button>
                                            {editingCategory && (
                                                <Button
                                                    type={'button'}
                                                    isSecondary
                                                    onClick={() => setEditingCategory(null)}
                                                    className='w-full'
                                                >
                                                    {t('categories.discard-changes')}
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </Form>
                            )}
                        </Formik>
                    </Card>
                </Column>

                {/* RIGHT SECTION: ARRANGE */}
                <Column>
                    <div className='flex items-center justify-between mb-5'>
                        <div className='flex items-center gap-2'>
                            <div className='bg-purple-500/10 p-2 rounded-ui'>
                                <FaArrowDownWideShort className='text-purple-400' />
                            </div>
                            <h3 className='text-lg font-semibold text-gray-200'>{t('categories.arrange-order')}</h3>
                        </div>
                        <span className='text-xs text-gray-400 bg-gray-800 px-2.5 py-1 rounded-ui border border-gray-600'>
                            {t('categories.categories-count', { count: categories.length })}
                        </span>
                    </div>

                    <div style={{ paddingRight: '0.5rem' }}>
                        {categories.length === 0 ? (
                            <Card className='!border-2 !border-dashed !p-12 text-center'>
                                <FaLayerGroup className='text-3xl text-gray-500 mb-4' />
                                <p className='text-sm text-gray-400'>{t('categories.no-custom-categories')}</p>
                            </Card>
                        ) : (
                            <DragDropContext onDragEnd={onDragEnd}>
                                <Droppable droppableId='categories'>
                                    {(provided) => (
                                        <div {...provided.droppableProps} ref={provided.innerRef}>
                                            {categories.map((cat, index) => (
                                                <Draggable key={cat.uuid} draggableId={cat.uuid} index={index}>
                                                    {(provided, snapshot) => (
                                                        <DragItem
                                                            className='bg-gray-700 border border-gray-600 rounded-ui'
                                                            ref={provided.innerRef}
                                                            {...provided.draggableProps}
                                                            {...provided.dragHandleProps}
                                                            isDragging={snapshot.isDragging}
                                                        >
                                                            <div
                                                                style={{
                                                                    display: 'flex',
                                                                    alignItems: 'center',
                                                                    minWidth: 0,
                                                                }}
                                                            >
                                                                <FaBars
                                                                    className='text-gray-500'
                                                                    style={{
                                                                        marginRight: '0.75rem',
                                                                        cursor: 'grab',
                                                                    }}
                                                                />
                                                                <div
                                                                    style={{
                                                                        backgroundColor: cat.color || '#3b82f6',
                                                                        width: '1.25rem',
                                                                        height: '1.25rem',
                                                                        borderRadius: '0.4rem',
                                                                        marginRight: '0.75rem',
                                                                        flexShrink: 0,
                                                                        border: '1px solid rgba(255,255,255,0.1)',
                                                                    }}
                                                                />
                                                                <div style={{ minWidth: 0 }}>
                                                                    <p
                                                                        style={{
                                                                            fontWeight: 600,
                                                                            color: '#f3f4f6',
                                                                            overflow: 'hidden',
                                                                            textOverflow: 'ellipsis',
                                                                            whiteSpace: 'nowrap',
                                                                            fontSize: '0.95rem',
                                                                        }}
                                                                    >
                                                                        {cat.name}
                                                                    </p>
                                                                    <p
                                                                        style={{
                                                                            fontSize: '0.7rem',
                                                                            color: '#9ca3af',
                                                                            overflow: 'hidden',
                                                                            textOverflow: 'ellipsis',
                                                                            whiteSpace: 'nowrap',
                                                                        }}
                                                                    >
                                                                        {cat.description ||
                                                                            t('categories.no-description')}
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <div
                                                                className='mt-3 sm:mt-0 w-full sm:w-auto justify-end'
                                                                style={{
                                                                    display: 'flex',
                                                                    alignItems: 'center',
                                                                    gap: '0.4rem',
                                                                    flexShrink: 0,
                                                                }}
                                                            >
                                                                <button
                                                                    type={'button'}
                                                                    onClick={(e) => {
                                                                        e.stopPropagation();
                                                                        setEditingCategory(cat);
                                                                    }}
                                                                    style={{
                                                                        padding: '0.5rem',
                                                                        borderRadius: '0.5rem',
                                                                        backgroundColor: snapshot.isDragging
                                                                            ? '#475569'
                                                                            : '#1e293b',
                                                                        border: '1px solid #334155',
                                                                        color: '#60a5fa',
                                                                        cursor: 'pointer',
                                                                    }}
                                                                    title={t('categories.edit')}
                                                                >
                                                                    <FaPen className='text-sm' />
                                                                </button>
                                                                <button
                                                                    type={'button'}
                                                                    onClick={(e) => {
                                                                        e.stopPropagation();
                                                                        handleDelete(cat.uuid);
                                                                    }}
                                                                    style={{
                                                                        padding: '0.5rem',
                                                                        borderRadius: '0.5rem',
                                                                        backgroundColor: snapshot.isDragging
                                                                            ? '#475569'
                                                                            : '#1e293b',
                                                                        border: '1px solid #334155',
                                                                        color: '#f87171',
                                                                        cursor: 'pointer',
                                                                    }}
                                                                    title={t('categories.delete')}
                                                                >
                                                                    <FaTrash className='text-sm' />
                                                                </button>
                                                            </div>
                                                        </DragItem>
                                                    )}
                                                </Draggable>
                                            ))}
                                            {provided.placeholder}
                                        </div>
                                    )}
                                </Droppable>
                            </DragDropContext>
                        )}
                    </div>
                </Column>
            </ResponsiveLayout>
        </Modal>
    );
};
