import React, { useEffect, useState } from 'react';
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
import styled from 'styled-components/macro';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faTrash, faPen, faPlus, faLayerGroup, faSortAmountDown, faBars } from '@fortawesome/free-solid-svg-icons';
import { DragDropContext, Droppable, Draggable, DropResult } from 'react-beautiful-dnd';
import { useTranslation } from 'react-i18next';

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
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem;
    background-color: ${props => props.isDragging ? '#334155' : '#1e293b'};
    border: 1px solid ${props => props.isDragging ? '#60a5fa' : '#334155'};
    border-radius: 0.85rem;
    margin-bottom: 0.75rem;
    transition: background-color 0.2s ease, border-color 0.2s ease;
    box-shadow: ${props => props.isDragging ? '0 10px 15px -3px rgba(0, 0, 0, 0.4)' : 'none'};
    cursor: grab;

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
            .then(data => setCategories(data))
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

        action.then(() => {
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
        newCategories.splice(result.destination.index, 0, reorderedItem);

        setCategories(newCategories);
        reorderServerCategories(newCategories.map(c => c.uuid))
            .then(() => onCategoryChanged())
            .catch((error: any) => clearAndAddHttpError({ key: 'category-modal', error }));
    };

    return (
        <Modal visible={visible} onDismissed={onDismissed} dismissable={true} size={'lg'} noScroll={true}>
            <div style={{ marginBottom: '1.5rem', borderBottom: '1px solid #1f2937', paddingBottom: '1rem' }}>
                <h2 style={{ fontSize: '1.5rem', fontWeight: 600, color: '#f3f4f6' }}>{t('categories.manage-title')}</h2>
            </div>

            <div style={{ marginBottom: '1.5rem' }}>
                <FlashMessageRender byKey={'category-modal'} />
            </div>

            <ResponsiveLayout>
                {/* LEFT SECTION: CREATE/EDIT */}
                <Column>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '1.25rem' }}>
                        <div style={{ backgroundColor: 'rgba(59, 130, 246, 0.1)', padding: '0.5rem', borderRadius: '0.6rem' }}>
                            <FontAwesomeIcon icon={editingCategory ? faPen : faPlus} style={{ color: '#60a5fa' }} />
                        </div>
                        <h3 style={{ fontSize: '1.125rem', fontWeight: 600, color: '#e5e7eb' }}>
                            {editingCategory ? t('categories.modify-category') : t('categories.create-category')}
                        </h3>
                    </div>

                    <div style={{ backgroundColor: '#111827', border: '1px solid #1f2937', borderRadius: '1rem', padding: '1.5rem', boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)' }}>
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
                                color: string().matches(/^#([a-f0-9]{6}|[a-f0-9]{3})$/i, t('categories.color-invalid')).max(7),
                            })}
                            enableReinitialize
                        >
                            {({ isSubmitting, values }) => (
                                <Form>
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
                                        <Field name={'name'} label={t('categories.category-name')} placeholder={t('categories.name-placeholder')} />

                                        <div style={{ display: 'flex', alignItems: 'flex-end', gap: '0.75rem' }}>
                                            <div style={{ flex: '1 1 0%' }}>
                                                <Field
                                                    name={'color'}
                                                    label={t('categories.theme-color')}
                                                    type={'color'}
                                                    style={{ height: '42px', padding: '0.2rem' }}
                                                />
                                            </div>
                                            <div style={{ flex: 'none', paddingBottom: '0.5rem', fontSize: '0.75rem', color: '#6b7280', display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                                                <div style={{ backgroundColor: values.color || '#3b82f6', width: '0.75rem', height: '0.75rem', borderRadius: '9999px', transition: 'background-color 0.2s ease' }}></div>
                                                {t('categories.preview')}
                                            </div>
                                        </div>

                                        <Field name={'description'} label={t('categories.description')} placeholder={t('categories.description-placeholder')} />

                                        <div style={{ marginTop: '0.5rem', display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                                            <Button type={'submit'} disabled={isSubmitting} isLoading={isSubmitting} style={{ width: '100%' }}>
                                                {editingCategory ? t('categories.update-category') : t('categories.create-category-button')}
                                            </Button>
                                            {editingCategory && (
                                                <Button type={'button'} isSecondary onClick={() => setEditingCategory(null)} style={{ width: '100%' }}>
                                                    {t('categories.discard-changes')}
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </Form>
                            )}
                        </Formik>
                    </div>
                </Column>

                {/* RIGHT SECTION: ARRANGE */}
                <Column>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1.25rem' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                            <div style={{ backgroundColor: 'rgba(139, 92, 246, 0.1)', padding: '0.5rem', borderRadius: '0.6rem' }}>
                                <FontAwesomeIcon icon={faSortAmountDown} style={{ color: '#a78bfa' }} />
                            </div>
                            <h3 style={{ fontSize: '1.125rem', fontWeight: 600, color: '#e5e7eb' }}>{t('categories.arrange-order')}</h3>
                        </div>
                        <span style={{ fontSize: '0.75rem', color: '#6b7280', backgroundColor: '#111827', padding: '0.25rem 0.6rem', borderRadius: '0.5rem', border: '1px solid #1f2937' }}>
                            {t('categories.categories-count', { count: categories.length })}
                        </span>
                    </div>

                    <div style={{ paddingRight: '0.5rem' }}>
                        {categories.length === 0 ? (
                            <div style={{ backgroundColor: '#111827', border: '2px dashed #1f2937', borderRadius: '1rem', padding: '3rem 1rem', textAlign: 'center' }}>
                                <FontAwesomeIcon icon={faLayerGroup} style={{ fontSize: '2rem', color: '#374151', marginBottom: '1rem' }} />
                                <p style={{ fontSize: '0.875rem', color: '#6b7280' }}>{t('categories.no-custom-categories')}</p>
                            </div>
                        ) : (
                            <DragDropContext onDragEnd={onDragEnd}>
                                <Droppable droppableId="categories">
                                    {(provided) => (
                                        <div {...provided.droppableProps} ref={provided.innerRef}>
                                            {categories.map((cat, index) => (
                                                <Draggable key={cat.uuid} draggableId={cat.uuid} index={index}>
                                                    {(provided, snapshot) => (
                                                        <DragItem
                                                            ref={provided.innerRef}
                                                            {...provided.draggableProps}
                                                            {...provided.dragHandleProps}
                                                            isDragging={snapshot.isDragging}
                                                        >
                                                            <div style={{ display: 'flex', alignItems: 'center', minWidth: 0 }}>
                                                                <FontAwesomeIcon icon={faBars} style={{ color: '#374151', marginRight: '0.75rem', cursor: 'grab' }} />
                                                                <div style={{ backgroundColor: cat.color || '#3b82f6', width: '1.25rem', height: '1.25rem', borderRadius: '0.4rem', marginRight: '0.75rem', flexShrink: 0, border: '1px solid rgba(255,255,255,0.1)' }} />
                                                                <div style={{ minWidth: 0 }}>
                                                                    <p style={{ fontWeight: 600, color: '#f3f4f6', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontSize: '0.95rem' }}>{cat.name}</p>
                                                                    <p style={{ fontSize: '0.7rem', color: '#9ca3af', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{cat.description || t('categories.no-description')}</p>
                                                                </div>
                                                            </div>

                                                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', flexShrink: 0 }}>
                                                                <button
                                                                    type={'button'}
                                                                    onClick={(e) => { e.stopPropagation(); setEditingCategory(cat); }}
                                                                    style={{ padding: '0.5rem', borderRadius: '0.5rem', backgroundColor: snapshot.isDragging ? '#475569' : '#1e293b', border: '1px solid #334155', color: '#60a5fa', cursor: 'pointer' }}
                                                                    title={t('categories.edit')}
                                                                >
                                                                    <FontAwesomeIcon icon={faPen} size={'sm'} />
                                                                </button>
                                                                <button
                                                                    type={'button'}
                                                                    onClick={(e) => { e.stopPropagation(); handleDelete(cat.uuid); }}
                                                                    style={{ padding: '0.5rem', borderRadius: '0.5rem', backgroundColor: snapshot.isDragging ? '#475569' : '#1e293b', border: '1px solid #334155', color: '#f87171', cursor: 'pointer' }}
                                                                    title={t('categories.delete')}
                                                                >
                                                                    <FontAwesomeIcon icon={faTrash} size={'sm'} />
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
