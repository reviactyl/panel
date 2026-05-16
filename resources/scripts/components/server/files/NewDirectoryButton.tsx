import { useContext, useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import { Form, Formik, FormikHelpers } from 'formik';
import Field from '@/reviactyl/elements/Field';
import { join } from 'pathe';
import { object, string } from 'yup';
import createDirectory from '@/api/server/files/createDirectory';
import tw from 'twin.macro';
import { Button } from '@/reviactyl/elements/button/index';
import { FileObject } from '@/api/server/files/loadDirectory';
import { useFlashKey } from '@/plugins/useFlash';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { WithClassname } from '@/components/types';
import FlashMessageRender from '@/components/FlashMessageRender';
import { Dialog, DialogWrapperContext } from '@/reviactyl/elements/dialog';
import Code from '@/reviactyl/elements/Code';
import asDialog from '@/hoc/asDialog';
import { useTranslation } from 'react-i18next';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { FaFolderPlus } from 'react-icons/fa6';

interface Values {
    directoryName: string;
}

const schema = object().shape({
    directoryName: string().required('A valid directory name must be provided.'),
});

const generateDirectoryData = (name: string): FileObject => ({
    key: `dir_${name.split('/', 1)[0] ?? name}`,
    name: name.replace(/^(\/*)/, '').split('/', 1)[0] ?? name,
    mode: 'drwxr-xr-x',
    modeBits: '0755',
    size: 0,
    isFile: false,
    isSymlink: false,
    mimetype: '',
    createdAt: new Date(),
    modifiedAt: new Date(),
    isArchiveType: () => false,
    isEditable: () => false,
});

const NewDirectoryDialog = asDialog({})(() => {
    const { t } = useTranslation('server/files');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    const { mutate } = useFileManagerSwr();
    const { close, setProps } = useContext(DialogWrapperContext);
    const { clearAndAddHttpError } = useFlashKey('files:directory-modal');

    useEffect(() => {
        setProps({ title: t('create-directory') });
    }, [setProps, t]);

    useEffect(() => {
        return () => {
            clearAndAddHttpError();
        };
    }, []);

    const submit = ({ directoryName }: Values, { setSubmitting }: FormikHelpers<Values>) => {
        createDirectory(uuid, directory, directoryName)
            .then(() => mutate((data) => [...data, generateDirectoryData(directoryName)], false))
            .then(() => close())
            .catch((error) => {
                setSubmitting(false);
                clearAndAddHttpError(error);
            });
    };

    return (
        <Formik
            onSubmit={submit}
            validationSchema={schema}
            validateOnChange={false}
            validateOnBlur={false}
            initialValues={{ directoryName: '' }}
        >
            {({ submitForm, values }) => (
                <>
                    <FlashMessageRender key={'files:directory-modal'} />
                    <Form css={tw`m-0`}>
                        <Field
                            autoFocus
                            id={'directoryName'}
                            name={'directoryName'}
                            label={t('directory-name-label')}
                        />
                        <p css={tw`mt-2 text-sm md:text-base break-all`}>
                            <span css={tw`text-gray-200`}>{t('directory-created-as')}&nbsp;</span>
                            <Code>
                                /home/container
                                <span css={tw`text-cyan-200`}>
                                    /{join(directory, values.directoryName).replace(/^(\.\.\/|\/)+/, '')}
                                </span>
                            </Code>
                        </p>
                    </Form>
                    <Dialog.Footer>
                        <Button.Text className={'w-full sm:w-auto'} onClick={close}>
                            {t('cancel')}
                        </Button.Text>
                        <Button className={'w-full sm:w-auto'} onClick={submitForm}>
                            {t('create')}
                        </Button>
                    </Dialog.Footer>
                </>
            )}
        </Formik>
    );
});

export default ({ className }: WithClassname & { compact?: boolean }) => {
    const { t } = useTranslation('server/files');
    const [open, setOpen] = useState(false);

    return (
        <>
            <NewDirectoryDialog open={open} onClose={setOpen.bind(this, false)} />
            <Tooltip content={t('create-directory')}>
                <Button.Text
                    onClick={setOpen.bind(this, true)}
                    className={className}
                    aria-label={t('create-directory')}
                >
                    <FaFolderPlus className='h-5 w-5' />
                </Button.Text>
            </Tooltip>
        </>
    );
};
