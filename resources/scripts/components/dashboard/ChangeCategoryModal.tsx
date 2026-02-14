import React from 'react';
import Modal from '@/components/elements/Modal';
import { Form, Formik, Field } from 'formik';
import { object, string } from 'yup';
import useFlash from '@/plugins/useFlash';
import { Server } from '@/api/server/getServer';
import getServerCategories from '@/api/account/getServerCategories';
import useSWR from 'swr';
import Button from '@/components/elements/Button';
import Spinner from '@/components/elements/Spinner';
import tw from 'twin.macro';
import Select from '@/components/elements/Select';
import updateServerCategory from '@/api/server/updateServerCategory';
import { useTranslation } from 'react-i18next';

interface Props {
    server: Server;
    visible: boolean;
    onDismissed: () => void;
}

export default ({ server, visible, onDismissed }: Props) => {
    const { t } = useTranslation('dashboard/index');
    const { addError, clearFlashes } = useFlash();
    const { data: categories } = useSWR(
        '/api/client/account/categories',
        () => getServerCategories()
    );

    const submit = (values: { category: string }, { setSubmitting }: any) => {
        clearFlashes('server:category');
        updateServerCategory(server.uuid, values.category)
            .then(() => {
                setSubmitting(false);
                onDismissed();
            })
            .catch(error => {
                setSubmitting(false);
                addError({ key: 'server:category', message: error });
            });
    };

    return (
        <Modal visible={visible} onDismissed={onDismissed} showSpinnerOverlay={false}>
            <h2 css={tw`text-2xl mb-4 font-bold`}>{t('categories.assign-category')}</h2>
            <Formik
                onSubmit={submit}
                initialValues={{ category: server.category?.uuid || '' }}
                validationSchema={object().shape({
                    category: string().nullable(),
                })}
            >
                {({ isSubmitting }) => (
                    <Form>
                        {!categories ? (
                            <Spinner centered />
                        ) : (
                            <div css={tw`mt-6`}>
                                <label css={tw`block text-sm mb-2 text-gray-300`}>{t('categories.select-category')}</label>
                                <Field name={'category'} as={Select}>
                                    <option value="">{t('categories.uncategorized')}</option>
                                    {categories.map(cat => (
                                        <option key={cat.uuid} value={cat.uuid}>
                                            {cat.name}
                                        </option>
                                    ))}
                                </Field>
                            </div>
                        )}
                        <div css={tw`flex flex-wrap justify-end mt-6`}>
                            <Button type={'submit'} disabled={isSubmitting} isLoading={isSubmitting}>
                                {t('categories.save-changes')}
                            </Button>
                        </div>
                    </Form>
                )}
            </Formik>
        </Modal>
    );
};
