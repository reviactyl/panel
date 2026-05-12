import { useState } from 'react';
import { FaDatabase, FaEye, FaTrash } from 'react-icons/fa6';
import Modal from '@/reviactyl/elements/Modal';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/reviactyl/elements/Field';
import { object, string } from 'yup';
import FlashMessageRender from '@/components/FlashMessageRender';
import { ServerContext } from '@/state/server';
import deleteServerDatabase from '@/api/server/databases/deleteServerDatabase';
import { httpErrorToHuman } from '@/api/http';
import RotatePasswordButton from '@/components/server/databases/RotatePasswordButton';
import Can from '@/reviactyl/elements/Can';
import { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import useFlash from '@/plugins/useFlash';
import tw from 'twin.macro';
import Button from '@/reviactyl/elements/Button';
import Label from '@/reviactyl/elements/Label';
import Input from '@/reviactyl/elements/Input';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';
import { ExtensionSlot } from '@/extensions/ExtensionSlot';
import { useTranslation } from 'react-i18next';

interface Props {
    database: ServerDatabase;
    className?: string;
}

export default ({ database, className }: Props) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { t } = useTranslation('server/databases');
    const { addError, clearFlashes } = useFlash();
    const [visible, setVisible] = useState(false);
    const [connectionVisible, setConnectionVisible] = useState(false);

    const appendDatabase = ServerContext.useStoreActions((actions) => actions.databases.appendDatabase);
    const removeDatabase = ServerContext.useStoreActions((actions) => actions.databases.removeDatabase);

    const jdbcConnectionString = `jdbc:mysql://${database.username}${
        database.password ? `:${encodeURIComponent(database.password)}` : ''
    }@${database.connectionString}/${database.name}`;

    const schema = object().shape({
        confirm: string()
            .required(t('confirm-name-required'))
            .oneOf(
                [database.name.split('_', 2)[1] ?? database.name, database.name],
                t('confirm-name-required')
            ),
    });

    const submit = (_: { confirm: string }, { setSubmitting }: FormikHelpers<{ confirm: string }>) => {
        clearFlashes();
        deleteServerDatabase(uuid, database.id)
            .then(() => {
                setVisible(false);
                setTimeout(() => removeDatabase(database.id), 150);
            })
            .catch((error) => {
                console.error(error);
                setSubmitting(false);
                addError({ key: 'database:delete', message: httpErrorToHuman(error) });
            });
    };

    return (
        <>
            <Formik onSubmit={submit} initialValues={{ confirm: '' }} validationSchema={schema} isInitialValid={false}>
                {({ isSubmitting, isValid, resetForm }) => (
                    <Modal
                        visible={visible}
                        dismissable={!isSubmitting}
                        showSpinnerOverlay={isSubmitting}
                        onDismissed={() => {
                            setVisible(false);
                            resetForm();
                        }}
                    >
                        <FlashMessageRender byKey={'database:delete'} css={tw`mb-6`} />
                        <h2 css={tw`text-2xl mb-6`}>{t('delete-title')}</h2>
                        <p css={tw`text-sm`}>
                            {t('delete-description')}
                            <strong>{database.name}</strong> {t('delete-description-tail')}
                        </p>
                        <Form css={tw`m-0 mt-6`}>
                            <Field
                                type={'text'}
                                id={'confirm_name'}
                                name={'confirm'}
                                label={t('confirm-name')}
                                description={t('confirm-name-description')}
                            />
                            <div css={tw`mt-6 text-right`}>
                                <Button type={'button'} isSecondary css={tw`mr-2`} onClick={() => setVisible(false)}>
                                    {t('cancel')}
                                </Button>
                                <Button type={'submit'} color={'red'} disabled={!isValid}>
                                    {t('delete-database')}
                                </Button>
                            </div>
                        </Form>
                    </Modal>
                )}
            </Formik>
            <Modal visible={connectionVisible} onDismissed={() => setConnectionVisible(false)}>
                <FlashMessageRender byKey={'database-connection-modal'} css={tw`mb-6`} />
                <h3 css={tw`mb-6 text-2xl`}>{t('connection-title')}</h3>
                <div>
                    <Label>{t('endpoint')}</Label>
                    <CopyOnClick text={database.connectionString}>
                        <Input type={'text'} readOnly value={database.connectionString} />
                    </CopyOnClick>
                </div>
                <div css={tw`mt-6`}>
                    <Label>{t('connections-from')}</Label>
                    <Input type={'text'} readOnly value={database.allowConnectionsFrom} />
                </div>
                <div css={tw`mt-6`}>
                    <Label>{t('username')}</Label>
                    <CopyOnClick text={database.username}>
                        <Input type={'text'} readOnly value={database.username} />
                    </CopyOnClick>
                </div>
                <Can action={'database.view_password'}>
                    <div css={tw`mt-6`}>
                        <Label>{t('password')}</Label>
                        <CopyOnClick text={database.password} showInNotification={false}>
                            <Input type={'text'} readOnly value={database.password} />
                        </CopyOnClick>
                    </div>
                </Can>
                <div css={tw`mt-6`}>
                    <Label>{t('jdbc-connection-string')}</Label>
                    <CopyOnClick text={jdbcConnectionString} showInNotification={false}>
                        <Input type={'text'} readOnly value={jdbcConnectionString} />
                    </CopyOnClick>
                </div>
                <div css={tw`mt-6 text-right`}>
                    <ExtensionSlot name={`server:databases:menu:start`} />
                    <Can action={'database.update'}>
                        <RotatePasswordButton databaseId={database.id} onUpdate={appendDatabase} />
                    </Can>
                    <Button isSecondary onClick={() => setConnectionVisible(false)}>
                        {t('close')}
                    </Button>
                    <ExtensionSlot name={`server:databases:menu:end`} />
                </div>
            </Modal>
            <GreyRowBox $hoverable={false} className={className} css={tw`mb-2`}>
                <div css={tw`hidden md:block`}>
                    <FaDatabase className={'inline-block w-[1.25em]'} />
                </div>
                <div css={tw`flex-1 ml-4`}>
                    <CopyOnClick text={database.name}>
                        <p css={tw`text-lg`}>{database.name}</p>
                    </CopyOnClick>
                </div>
                <div css={tw`ml-8 text-center hidden md:block`}>
                    <CopyOnClick text={database.connectionString}>
                        <p css={tw`text-sm`}>{database.connectionString}</p>
                    </CopyOnClick>
                    <p css={tw`mt-1 text-2xs text-gray-600 uppercase select-none`}>{t('endpoint')}</p>
                </div>
                <div css={tw`ml-8 text-center hidden md:block`}>
                    <p css={tw`text-sm`}>{database.allowConnectionsFrom}</p>
                    <p css={tw`mt-1 text-2xs text-gray-600 uppercase select-none`}>{t('connections-from')}</p>
                </div>
                <div css={tw`ml-8 text-center hidden md:block`}>
                    <CopyOnClick text={database.username}>
                        <p css={tw`text-sm`}>{database.username}</p>
                    </CopyOnClick>
                    <p css={tw`mt-1 text-2xs text-gray-600 uppercase select-none`}>{t('username')}</p>
                </div>
                <div css={tw`ml-8`}>
                    <Button isSecondary css={tw`mr-2`} onClick={() => setConnectionVisible(true)}>
                        <FaEye className={'inline-block w-[1.25em]'} />
                    </Button>
                    <Can action={'database.delete'}>
                        <Button color={'red'} isSecondary onClick={() => setVisible(true)}>
                            <FaTrash className={'inline-block w-[1.25em]'} />
                        </Button>
                    </Can>
                </div>
            </GreyRowBox>
        </>
    );
};
