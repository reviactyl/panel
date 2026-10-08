import { useRef, useState } from 'react';
import type { DragEvent } from 'react';
import { Form, Formik, FormikHelpers } from 'formik';
import { boolean, object, string } from 'yup';
import classNames from 'classnames';
import { FaFileArrowUp, FaFileImport, FaFileLines, FaXmark } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import Modal from '@/reviactyl/elements/Modal';
import Button from '@/reviactyl/elements/Button';
import Field from '@/reviactyl/elements/Field';
import FormikSwitch from '@/reviactyl/elements/FormikSwitch';
import FlashMessageRender from '@/components/FlashMessageRender';
import { ServerContext } from '@/state/server';
import { DatabaseImportStatus, ServerDatabase } from '@/api/server/databases/getServerDatabases';
import importServerDatabase from '@/api/server/databases/importServerDatabase';
import { httpErrorToHuman } from '@/api/http';
import { bytesToString } from '@/lib/formatters';
import useFlash from '@/plugins/useFlash';

interface Props {
    database: ServerDatabase;
    visible: boolean;
    onDismissed: () => void;
    onImport: (status: DatabaseImportStatus | null) => void;
}

interface Values {
    wipe: boolean;
    remote: boolean;
    host: string;
    port: string;
    database: string;
    username: string;
    password: string;
}

const initialValues: Values = {
    wipe: false,
    remote: false,
    host: '',
    port: '3306',
    database: '',
    username: '',
    password: '',
};

export default ({ database, visible, onDismissed, onImport }: Props) => {
    const { t } = useTranslation('server/databases');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useFlash();

    const input = useRef<HTMLInputElement>(null);
    const [file, setFile] = useState<File | null>(null);
    const [fileError, setFileError] = useState<string | null>(null);
    const [dragging, setDragging] = useState(false);
    const [progress, setProgress] = useState(0);

    const schema = object().shape({
        wipe: boolean(),
        remote: boolean(),
        host: string().when('remote', {
            is: true,
            then: string()
                .required(t('import-remote-host-required'))
                .matches(/^[\w\-.:[\]]+$/, t('import-remote-host-invalid')),
        }),
        port: string().when('remote', {
            is: true,
            then: string()
                .required(t('import-remote-port-invalid'))
                .test('port', t('import-remote-port-invalid'), (value) => {
                    return /^\d{1,5}$/.test(value || '') && Number(value) >= 1 && Number(value) <= 65535;
                }),
        }),
        database: string().when('remote', {
            is: true,
            then: string().required(t('import-remote-database-required')),
        }),
        username: string().when('remote', {
            is: true,
            then: string().required(t('import-remote-username-required')),
        }),
        password: string(),
    });

    const selectFile = (selected?: File | null) => {
        if (!selected) return;

        if (!/\.sql(\.(gz|zip))?$/i.test(selected.name)) {
            setFile(null);
            setFileError(t('import-file-invalid'));

            return;
        }

        setFile(selected);
        setFileError(null);
    };

    const onDrop = (e: DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        setDragging(false);
        selectFile(e.dataTransfer.files[0]);
    };

    const reset = (resetForm: () => void) => {
        resetForm();
        setFile(null);
        setFileError(null);
        setProgress(0);
        clearFlashes('database:import');
    };

    const submit = (values: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearFlashes('database:import');

        if (!values.remote && !file) {
            setFileError(t('import-file-required'));
            setSubmitting(false);

            return;
        }

        setProgress(0);
        importServerDatabase(
            uuid,
            database.id,
            values.remote
                ? {
                      wipe: values.wipe,
                      remote: {
                          host: values.host,
                          port: Number(values.port),
                          database: values.database,
                          username: values.username,
                          password: values.password,
                      },
                  }
                : { wipe: values.wipe, file: file! },
            setProgress,
        )
            .then((status) => {
                onImport(status);
                onDismissed();
                reset(resetForm);
            })
            .catch((error) => {
                console.error(error);
                addError({
                    key: 'database:import',
                    message: error?.response?.status === 413 ? t('import-file-too-large') : httpErrorToHuman(error),
                });
                setSubmitting(false);
            });
    };

    return (
        <Formik onSubmit={submit} initialValues={initialValues} validationSchema={schema}>
            {({ isSubmitting, values, resetForm }) => (
                <Modal
                    visible={visible}
                    dismissable={!isSubmitting}
                    showSpinnerOverlay={isSubmitting}
                    onDismissed={() => {
                        onDismissed();
                        reset(resetForm);
                    }}
                >
                    <FlashMessageRender byKey='database:import' className='mb-6' />
                    <h2 className='text-2xl'>{t('import-title')}</h2>
                    <p className='mt-2 text-sm text-gray-300'>{t('import-description', { name: database.name })}</p>
                    <Form className='m-0 mt-6'>
                        {!values.remote &&
                            (file ? (
                                <div className='rounded-ui border border-gray-800 bg-gray-900 p-4'>
                                    <div className='flex items-center'>
                                        <FaFileLines className='h-6 w-6 shrink-0 text-reviactyl' />
                                        <div className='ml-4 min-w-0 flex-1'>
                                            <p className='truncate text-sm text-gray-100'>{file.name}</p>
                                            <p className='mt-0.5 text-xs text-gray-400'>{bytesToString(file.size)}</p>
                                        </div>
                                        <button
                                            type='button'
                                            aria-label={t('import-file-remove')}
                                            title={t('import-file-remove')}
                                            className='ml-4 rounded-ui p-2 text-gray-400 transition-colors duration-150 hover:bg-gray-800 hover:text-gray-100'
                                            onClick={() => setFile(null)}
                                        >
                                            <FaXmark className='h-4 w-4' />
                                        </button>
                                    </div>
                                    {isSubmitting && (
                                        <div
                                            className='mt-4 h-1.5 overflow-hidden rounded-full bg-gray-700'
                                            role='progressbar'
                                            aria-valuemin={0}
                                            aria-valuemax={100}
                                            aria-valuenow={Math.round(progress * 100)}
                                        >
                                            <div
                                                className='h-full rounded-full bg-reviactyl transition-[width] duration-150'
                                                style={{ width: `${Math.round(progress * 100)}%` }}
                                            />
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div
                                    role='button'
                                    tabIndex={0}
                                    className={classNames(
                                        'flex cursor-pointer flex-col items-center justify-center rounded-ui border-2 border-dashed px-4 py-8 text-center transition-colors duration-150',
                                        dragging
                                            ? 'border-reviactyl bg-reviactyl/10'
                                            : fileError
                                              ? 'border-red-400/70 hover:border-red-300'
                                              : 'border-gray-700 hover:border-gray-500',
                                    )}
                                    onClick={() => input.current?.click()}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter' || e.key === ' ') {
                                            e.preventDefault();
                                            input.current?.click();
                                        }
                                    }}
                                    onDragOver={(e) => {
                                        e.preventDefault();
                                        setDragging(true);
                                    }}
                                    onDragLeave={() => setDragging(false)}
                                    onDrop={onDrop}
                                >
                                    <FaFileArrowUp className='h-8 w-8 text-gray-400' />
                                    <p className='mt-3 text-sm text-gray-100'>{t('import-file-drop')}</p>
                                    <p className='mt-1 text-xs text-gray-400'>{t('import-file-types')}</p>
                                    {fileError && <p className='mt-3 text-xs text-red-200'>{fileError}</p>}
                                </div>
                            ))}
                        <input
                            ref={input}
                            type='file'
                            accept='.sql,.gz,.zip'
                            className='hidden'
                            onChange={(e) => {
                                selectFile(e.currentTarget.files?.[0]);
                                e.currentTarget.value = '';
                            }}
                        />
                        <div
                            className={classNames(
                                'rounded-ui border p-4 transition-colors duration-150',
                                !values.remote && 'mt-4',
                                values.wipe ? 'border-red-500/40 bg-red-500/10' : 'border-gray-800 bg-gray-900',
                            )}
                        >
                            <FormikSwitch
                                name='wipe'
                                label={t('import-wipe')}
                                description={t('import-wipe-description')}
                            />
                        </div>
                        <div className='mt-4 rounded-ui border border-gray-800 bg-gray-900 p-4'>
                            <FormikSwitch
                                name='remote'
                                label={t('import-remote')}
                                description={t('import-remote-description')}
                            />
                            {values.remote && (
                                <div className='mt-4 grid grid-cols-1 gap-4 border-t border-gray-800 pt-4 sm:grid-cols-6'>
                                    <div className='sm:col-span-4'>
                                        <Field
                                            id='import_remote_host'
                                            name='host'
                                            label={t('import-remote-host')}
                                            placeholder='db.example.com'
                                            autoComplete='off'
                                        />
                                    </div>
                                    <div className='sm:col-span-2'>
                                        <Field
                                            id='import_remote_port'
                                            name='port'
                                            label={t('import-remote-port')}
                                            placeholder='3306'
                                            inputMode='numeric'
                                            autoComplete='off'
                                        />
                                    </div>
                                    <div className='sm:col-span-6'>
                                        <Field
                                            id='import_remote_database'
                                            name='database'
                                            label={t('import-remote-database')}
                                            autoComplete='off'
                                        />
                                    </div>
                                    <div className='sm:col-span-3'>
                                        <Field
                                            id='import_remote_username'
                                            name='username'
                                            label={t('import-remote-username')}
                                            autoComplete='off'
                                        />
                                    </div>
                                    <div className='sm:col-span-3'>
                                        <Field
                                            id='import_remote_password'
                                            name='password'
                                            type='password'
                                            label={t('import-remote-password')}
                                            autoComplete='new-password'
                                        />
                                    </div>
                                </div>
                            )}
                        </div>
                        <div className='mt-6 flex flex-wrap justify-end'>
                            <Button
                                type='button'
                                isSecondary
                                className='w-full sm:mr-2 sm:w-auto'
                                onClick={() => {
                                    onDismissed();
                                    reset(resetForm);
                                }}
                            >
                                {t('cancel')}
                            </Button>
                            <Button
                                type='submit'
                                color={values.wipe ? 'red' : undefined}
                                className='mt-4 w-full sm:mt-0 sm:w-auto'
                            >
                                <FaFileImport className='mr-2 inline-block w-[1.25em]' />
                                {values.wipe ? t('import-submit-wipe') : t('import-submit')}
                            </Button>
                        </div>
                    </Form>
                </Modal>
            )}
        </Formik>
    );
};
