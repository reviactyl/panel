import { useEffect, useRef, useState } from 'react';
import { FaDatabase, FaEye, FaFileExport, FaFileImport, FaTrash, FaTriangleExclamation } from 'react-icons/fa6';
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
import { DatabaseImportStatus, ServerDatabase } from '@/api/server/databases/getServerDatabases';
import getDatabaseImportStatus from '@/api/server/databases/getDatabaseImportStatus';
import ImportDatabaseModal from '@/components/server/databases/ImportDatabaseModal';
import ExportDatabaseModal from '@/components/server/databases/ExportDatabaseModal';
import Spinner from '@/reviactyl/elements/Spinner';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import useFlash from '@/plugins/useFlash';
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
    const { addError, addFlash, clearFlashes } = useFlash();
    const [visible, setVisible] = useState(false);
    const [connectionVisible, setConnectionVisible] = useState(false);
    const [importVisible, setImportVisible] = useState(false);
    const [exportVisible, setExportVisible] = useState(false);

    const appendDatabase = ServerContext.useStoreActions((actions) => actions.databases.appendDatabase);
    const removeDatabase = ServerContext.useStoreActions((actions) => actions.databases.removeDatabase);

    const importErrorMessage = (status?: DatabaseImportStatus | null) =>
        status?.state === 'failed'
            ? [t(`import-error-${status.error}`, { defaultValue: t('import-error-unknown') }), status.detail]
                  .filter(Boolean)
                  .join(' ')
            : null;

    const importing = database.importStatus?.state === 'running';
    const importError = importErrorMessage(database.importStatus);

    const current = useRef(database);
    current.current = database;

    const onImportStatus = (status: DatabaseImportStatus | null) => {
        appendDatabase({ ...current.current, importStatus: status });

        if (status?.state === 'completed') {
            clearFlashes('databases');
            addFlash({
                key: 'databases',
                type: 'success',
                message: t('import-completed', { name: database.name, statements: status.statements }),
            });
        }

        if (status?.state === 'failed') {
            clearFlashes('databases');
            addError({ key: 'databases', message: `${t('import-failed')}: ${importErrorMessage(status)}` });
        }
    };

    useEffect(() => {
        if (!importing) return;

        let cancelled = false;
        let timeout: ReturnType<typeof setTimeout>;

        const poll = () => {
            timeout = setTimeout(() => {
                getDatabaseImportStatus(uuid, database.id)
                    .then((status) => !cancelled && onImportStatus(status))
                    .catch((error) => console.error(error))
                    .then(() => !cancelled && poll());
            }, 2500);
        };

        poll();

        return () => {
            cancelled = true;
            clearTimeout(timeout);
        };
    }, [importing, uuid, database.id]);

    const jdbcConnectionString = `jdbc:mysql://${database.username}${
        database.password ? `:${encodeURIComponent(database.password)}` : ''
    }@${database.connectionString}/${database.name}`;

    const schema = object().shape({
        confirm: string()
            .required(t('confirm-name-required'))
            .oneOf([database.name.split('_', 2)[1] ?? database.name, database.name], t('confirm-name-required')),
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
                        <FlashMessageRender byKey='database:delete' className='mb-6' />
                        <h2 className='mb-6 text-2xl'>{t('delete-title')}</h2>
                        <p className='text-sm'>
                            {t('delete-description')}
                            <strong>{database.name}</strong> {t('delete-description-tail')}
                        </p>
                        <Form className='m-0 mt-6'>
                            <Field
                                type={'text'}
                                id={'confirm_name'}
                                name={'confirm'}
                                label={t('confirm-name')}
                                description={t('confirm-name-description')}
                            />
                            <div className='mt-6 text-right'>
                                <Button type='button' isSecondary className='mr-2' onClick={() => setVisible(false)}>
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
                <FlashMessageRender byKey='database-connection-modal' className='mb-6' />
                <h3 className='mb-6 text-2xl'>{t('connection-title')}</h3>
                <div>
                    <Label>{t('endpoint')}</Label>
                    <CopyOnClick text={database.connectionString}>
                        <Input type={'text'} readOnly value={database.connectionString} />
                    </CopyOnClick>
                </div>
                <div className='mt-6'>
                    <Label>{t('connections-from')}</Label>
                    <Input type={'text'} readOnly value={database.allowConnectionsFrom} />
                </div>
                <div className='mt-6'>
                    <Label>{t('username')}</Label>
                    <CopyOnClick text={database.username}>
                        <Input type={'text'} readOnly value={database.username} />
                    </CopyOnClick>
                </div>
                <Can action={'database.view_password'}>
                    <div className='mt-6'>
                        <Label>{t('password')}</Label>
                        <CopyOnClick text={database.password} showInNotification={false}>
                            <Input type={'text'} readOnly value={database.password} />
                        </CopyOnClick>
                    </div>
                </Can>
                <div className='mt-6'>
                    <Label>{t('jdbc-connection-string')}</Label>
                    <CopyOnClick text={jdbcConnectionString} showInNotification={false}>
                        <Input type={'text'} readOnly value={jdbcConnectionString} />
                    </CopyOnClick>
                </div>
                <div className='mt-6 text-right'>
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
            <ImportDatabaseModal
                database={database}
                visible={importVisible}
                onDismissed={() => setImportVisible(false)}
                onImport={onImportStatus}
            />
            <ExportDatabaseModal
                database={database}
                visible={exportVisible}
                onDismissed={() => setExportVisible(false)}
            />
            <GreyRowBox $hoverable={false} className={`mb-2 ${className || ''}`}>
                <div className='hidden md:block'>
                    <FaDatabase className={'inline-block w-[1.25em]'} />
                </div>
                <div className='ml-4 flex-1'>
                    <CopyOnClick text={database.name}>
                        <p className='text-lg'>{database.name}</p>
                    </CopyOnClick>
                    {importing && (
                        <p className='mt-1 flex items-center text-xs text-gray-300' role='status'>
                            <Spinner size={'small'} className='mr-2 !h-3 !w-3' />
                            {t('importing', { statements: database.importStatus?.statements ?? 0 })}
                        </p>
                    )}
                    {importError && (
                        <Tooltip content={importError} placement='bottom-start' className='max-w-md'>
                            <p className='mt-1 inline-flex cursor-help items-center text-xs text-red-400'>
                                <FaTriangleExclamation className='mr-1.5 h-3 w-3' />
                                {t('import-failed')}
                            </p>
                        </Tooltip>
                    )}
                </div>
                <div className='ml-8 hidden text-center md:block'>
                    <CopyOnClick text={database.connectionString}>
                        <p className='text-sm'>{database.connectionString}</p>
                    </CopyOnClick>
                    <p className='mt-1 select-none text-2xs uppercase text-muted'>{t('endpoint')}</p>
                </div>
                <div className='ml-8 hidden text-center md:block'>
                    <p className='text-sm'>{database.allowConnectionsFrom}</p>
                    <p className='mt-1 select-none text-2xs uppercase text-muted'>{t('connections-from')}</p>
                </div>
                <div className='ml-8 hidden text-center md:block'>
                    <CopyOnClick text={database.username}>
                        <p className='text-sm'>{database.username}</p>
                    </CopyOnClick>
                    <p className='mt-1 select-none text-2xs uppercase text-muted'>{t('username')}</p>
                </div>
                <div className='ml-8'>
                    <Button
                        isSecondary
                        className='mr-2'
                        title={t('connection-title')}
                        aria-label={t('connection-title')}
                        onClick={() => setConnectionVisible(true)}
                    >
                        <FaEye className={'inline-block w-[1.25em]'} />
                    </Button>
                    <Can action={'database.import'}>
                        <Button
                            isSecondary
                            className='mr-2'
                            title={t('import-title')}
                            aria-label={t('import-title')}
                            disabled={importing}
                            onClick={() => setImportVisible(true)}
                        >
                            <FaFileImport className={'inline-block w-[1.25em]'} />
                        </Button>
                    </Can>
                    <Can action={'database.export'}>
                        <Button
                            isSecondary
                            className='mr-2'
                            title={t('export-title')}
                            aria-label={t('export-title')}
                            disabled={importing}
                            onClick={() => setExportVisible(true)}
                        >
                            <FaFileExport className={'inline-block w-[1.25em]'} />
                        </Button>
                    </Can>
                    <Can action={'database.delete'}>
                        <Button
                            color={'red'}
                            isSecondary
                            title={t('delete-database')}
                            aria-label={t('delete-database')}
                            disabled={importing}
                            onClick={() => setVisible(true)}
                        >
                            <FaTrash className={'inline-block w-[1.25em]'} />
                        </Button>
                    </Can>
                </div>
            </GreyRowBox>
        </>
    );
};
