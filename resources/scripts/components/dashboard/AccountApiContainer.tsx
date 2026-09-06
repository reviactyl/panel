import { useEffect, useState } from 'react';
import ContentBox from '@/reviactyl/elements/ContentBox';
import CreateApiKeyForm from '@/components/dashboard/forms/CreateApiKeyForm';
import getApiKeys, { ApiKey } from '@/api/account/getApiKeys';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import deleteApiKey from '@/api/account/deleteApiKey';
import FlashMessageRender from '@/components/FlashMessageRender';
import { format } from 'date-fns';
import PageContentBlock from '@/reviactyl/elements/PageContentBlock';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import { Dialog } from '@/reviactyl/elements/dialog';
import { useFlashKey } from '@/plugins/useFlash';
import Code from '@/reviactyl/elements/Code';
import { useTranslation } from 'react-i18next';
import { FaKey, FaTrash } from 'react-icons/fa6';

export default () => {
    const { t } = useTranslation('dashboard/account');
    const [deleteIdentifier, setDeleteIdentifier] = useState('');
    const [keys, setKeys] = useState<ApiKey[]>([]);
    const [loading, setLoading] = useState(true);
    const { clearAndAddHttpError } = useFlashKey('account');

    useEffect(() => {
        getApiKeys()
            .then((keys) => setKeys(keys))
            .then(() => setLoading(false))
            .catch((error) => clearAndAddHttpError(error));
    }, []);

    const doDeletion = (identifier: string) => {
        setLoading(true);

        clearAndAddHttpError();
        deleteApiKey(identifier)
            .then(() => setKeys((s) => [...(s || []).filter((key) => key.identifier !== identifier)]))
            .catch((error) => clearAndAddHttpError(error))
            .then(() => {
                setLoading(false);
                setDeleteIdentifier('');
            });
    };

    return (
        <PageContentBlock title={t('api.title')}>
            <FlashMessageRender byKey={'account'} />
            <div className='my-10 flex-nowrap md:flex'>
                <ContentBox title={t('api.create-key')} className='w-full flex-none md:w-1/2'>
                    <CreateApiKeyForm onKeyCreated={(key) => setKeys((s) => [...s!, key])} />
                </ContentBox>
                <ContentBox title={t('api.content-title')} className='mt-8 flex-1 overflow-hidden md:mt-0 md:ml-8'>
                    <SpinnerOverlay visible={loading} />
                    <Dialog.Confirm
                        title={t('api.delete')}
                        confirm={t('api.delete')}
                        open={!!deleteIdentifier}
                        onClose={() => setDeleteIdentifier('')}
                        onConfirmed={() => doDeletion(deleteIdentifier)}
                    >
                        {t('api.info')} (<Code>{deleteIdentifier}</Code>)
                    </Dialog.Confirm>
                    {keys.length === 0 ? (
                        <p className='text-center text-sm'>{loading ? t('overview.loading') : t('api.not-exist')}</p>
                    ) : (
                        keys.map((key, index) => (
                            <GreyRowBox key={key.identifier} className={`flex items-center ${index > 0 ? 'mt-2' : ''}`}>
                                <FaKey className='text-gray-300' />
                                <div className='ml-4 flex-1 overflow-hidden'>
                                    <p className='break-words text-sm'>{key.description}</p>
                                    <p className='text-2xs uppercase text-gray-300'>
                                        {t('api.last-used')}:&nbsp;
                                        {key.lastUsedAt
                                            ? format(key.lastUsedAt, 'MMM do, yyyy HH:mm')
                                            : t('api.never-used')}
                                    </p>
                                </div>
                                <p className='ml-4 hidden text-sm md:block'>
                                    <code className='rounded-md bg-gray-950 px-2 py-1 font-mono'>{key.identifier}</code>
                                </p>
                                <button
                                    className='ml-4 p-2 text-sm'
                                    onClick={() => setDeleteIdentifier(key.identifier)}
                                >
                                    <FaTrash className='text-gray-400 transition-colors duration-150 hover:text-red-400' />
                                </button>
                            </GreyRowBox>
                        ))
                    )}
                </ContentBox>
            </div>
        </PageContentBlock>
    );
};
