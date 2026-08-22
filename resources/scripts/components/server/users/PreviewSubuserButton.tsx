import { useState } from 'react';
import { AxiosError } from 'axios';
import { FaEye } from 'react-icons/fa6';
import { Subuser } from '@/state/server/subusers';
import { ServerContext } from '@/state/server';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';
import { Dialog } from '@/reviactyl/elements/dialog';
import { httpErrorToHuman } from '@/api/http';
import { useTranslation } from 'react-i18next';

export default ({ subuser }: { subuser: Subuser }) => {
    const { t } = useTranslation('server/users');
    const serverUuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { session, start } = useSubuserPreview();
    const [confirmTakeover, setConfirmTakeover] = useState(false);
    const [message, setMessage] = useState('');
    const [loading, setLoading] = useState(false);

    const begin = async (replace = false) => {
        if (session) {
            setMessage(t('preview.start-blocked'));
            return;
        }

        setLoading(true);
        setMessage('');

        try {
            const status = await start(serverUuid, subuser.uuid, replace);
            if (status.session) {
                window.location.assign(`/server/${status.session.serverIdentifier}`);
            }
        } catch (error) {
            if (error instanceof AxiosError && error.response?.status === 409) {
                setConfirmTakeover(true);
            } else {
                setMessage(httpErrorToHuman(error));
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <Dialog.Confirm
                open={confirmTakeover}
                onClose={() => setConfirmTakeover(false)}
                onConfirmed={() => {
                    setConfirmTakeover(false);
                    void begin(true);
                }}
                title={t('preview.already-active-title')}
                confirm={t('preview.takeover-confirm')}
            >
                {t('preview.already-active-message')}
            </Dialog.Confirm>
            <Dialog open={message.length > 0} onClose={() => setMessage('')} title={t('preview.start-error-title')}>
                <p className='text-sm text-gray-200'>{message}</p>
            </Dialog>
            <button
                type='button'
                aria-label={t('preview.preview-as', { email: subuser.email })}
                title={t('preview.preview-as', { email: subuser.email })}
                disabled={loading}
                className='block p-1 text-sm text-gray-600 transition-colors duration-150 hover:text-amber-300 disabled:opacity-50 md:p-2'
                onClick={() => void begin()}
            >
                <FaEye />
            </button>
        </>
    );
};
