import { useState } from 'react';
import Modal from '@/reviactyl/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/reviactyl/elements/Field';
import { object, string } from 'yup';
import createServerDatabase from '@/api/server/databases/createServerDatabase';
import { ServerContext } from '@/state/server';
import { httpErrorToHuman } from '@/api/http';
import FlashMessageRender from '@/components/FlashMessageRender';
import useFlash from '@/plugins/useFlash';
import Button from '@/reviactyl/elements/Button';
import { useTranslation } from 'react-i18next';

interface Values {
    databaseName: string;
    connectionsFrom: string;
}

export const databaseCreationSchema = object().shape({
    databaseName: string()
        .required('A database name must be provided.')
        .test('min', 'Database name must be at least 3 characters.', (value) => Array.from(value).length >= 3)
        .test('max', 'Database name must not exceed 48 characters.', (value) => Array.from(value).length <= 48)
        .test(
            'alpha-dash',
            'Database name should only contain alphanumeric characters, underscores, and/or dashes.',
            (value) => !/[^\p{L}\p{M}\p{N}_-]/u.test(value),
        ),
    connectionsFrom: string().matches(/^[\w\-/.%:]+$/, {
        message: 'A valid host address must be provided.',
        excludeEmptyString: true,
    }),
});

export default () => {
    const { t } = useTranslation('server/databases');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useFlash();
    const [visible, setVisible] = useState(false);

    const appendDatabase = ServerContext.useStoreActions((actions) => actions.databases.appendDatabase);

    const submit = (values: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearFlashes('database:create');
        return createServerDatabase(uuid, {
            databaseName: values.databaseName,
            connectionsFrom: values.connectionsFrom || '%',
        })
            .then((database) => {
                appendDatabase(database);
                resetForm();
                setVisible(false);
            })
            .catch((error) => {
                addError({ key: 'database:create', message: httpErrorToHuman(error) });
                setSubmitting(false);
            });
    };

    return (
        <>
            <Formik
                onSubmit={submit}
                initialValues={{ databaseName: '', connectionsFrom: '' }}
                validationSchema={databaseCreationSchema}
            >
                {({ isSubmitting, resetForm }) => (
                    <Modal
                        visible={visible}
                        dismissable={!isSubmitting}
                        showSpinnerOverlay={isSubmitting}
                        onDismissed={() => {
                            resetForm();
                            setVisible(false);
                        }}
                    >
                        <FlashMessageRender byKey={'database:create'} className='mb-6' />
                        <h2 className='text-2xl mb-6'>{t('create-title')}</h2>
                        <Form className='m-0'>
                            <Field
                                type={'string'}
                                id={'database_name'}
                                name={'databaseName'}
                                label={t('name')}
                                description={t('name-description')}
                            />
                            <div className='mt-6'>
                                <Field
                                    type={'string'}
                                    id={'connections_from'}
                                    name={'connectionsFrom'}
                                    label={t('connections')}
                                    description={t('connections-description')}
                                />
                            </div>
                            <div className='flex flex-wrap justify-end mt-6'>
                                <Button
                                    type={'button'}
                                    isSecondary
                                    className='w-full sm:w-auto sm:mr-2'
                                    onClick={() => setVisible(false)}
                                >
                                    {t('cancel')}
                                </Button>
                                <Button className='w-full mt-4 sm:w-auto sm:mt-0' type={'submit'}>
                                    {t('create-database')}
                                </Button>
                            </div>
                        </Form>
                    </Modal>
                )}
            </Formik>
            <Button onClick={() => setVisible(true)}>{t('new-database')}</Button>
        </>
    );
};
