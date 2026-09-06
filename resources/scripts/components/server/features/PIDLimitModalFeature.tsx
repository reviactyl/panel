import { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import Modal from '@/reviactyl/elements/Modal';
import Button from '@/reviactyl/elements/Button';
import FlashMessageRender from '@/components/FlashMessageRender';
import useFlash from '@/plugins/useFlash';
import { SocketEvent } from '@/components/server/events';
import { useStoreState } from 'easy-peasy';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';
import { FaTriangleExclamation } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';

const PIDLimitModalFeature = () => {
    const [visible, setVisible] = useState(false);
    const [loading] = useState(false);
    const { t } = useTranslation('server/features');

    const status = ServerContext.useStoreState((state) => state.status.value);
    const { clearFlashes } = useFlash();
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);
    const accountAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const { session } = useSubuserPreview();
    const isAdmin = accountAdmin && !session;

    useEffect(() => {
        if (!connected || !instance || status === 'running') return;

        const errors = [
            'pthread_create failed',
            'failed to create thread',
            'unable to create thread',
            'unable to create native thread',
            'unable to create new native thread',
            'exception in thread "craft async scheduler management thread"',
        ];

        const listener = (line: string) => {
            if (errors.some((p) => line.toLowerCase().includes(p))) {
                setVisible(true);
            }
        };

        instance.addListener(SocketEvent.CONSOLE_OUTPUT, listener);

        return () => {
            instance.removeListener(SocketEvent.CONSOLE_OUTPUT, listener);
        };
    }, [connected, instance, status]);

    useEffect(() => {
        clearFlashes('feature:pidLimit');
    }, []);

    return (
        <Modal
            visible={visible}
            onDismissed={() => setVisible(false)}
            closeOnBackground={false}
            showSpinnerOverlay={loading}
        >
            <FlashMessageRender key={'feature:pidLimit'} className='mb-4' />
            {isAdmin ? (
                <>
                    <div className='mt-4 items-center sm:flex'>
                        <FaTriangleExclamation className='pr-4' color={'orange'} size={'4em'} />
                        <h2 className='mb-4 text-2xl text-gray-100'>Memory or process limit reached...</h2>
                    </div>
                    <p className='mt-4'>{t('pid-limit.message')}</p>
                    <p className='mt-4'>
                        Increasing <code className='bg-gray-950 font-mono'>container_pid_limit</code> in the agent
                        configuration, <code className='bg-gray-950 font-mono'>config.yml</code>, might help resolve
                        this issue.
                    </p>
                    <p className='mt-4'>
                        <b>Note: Agent must be restarted for the configuration file changes to take effect</b>
                    </p>
                    <div className='mt-8 items-center justify-end sm:flex'>
                        <Button onClick={() => setVisible(false)} className='w-full border-transparent sm:w-auto'>
                            Close
                        </Button>
                    </div>
                </>
            ) : (
                <>
                    <div className='mt-4 items-center sm:flex'>
                        <FaTriangleExclamation className='pr-4' color={'orange'} size={'4em'} />
                        <h2 className='mb-4 text-2xl text-gray-100'>Possible resource limit reached...</h2>
                    </div>
                    <p className='mt-4'>
                        This server is attempting to use more resources than allocated. Please contact the administrator
                        and give them the error below.
                    </p>
                    <p className='mt-4'>
                        <code className='bg-gray-950 font-mono'>
                            pthread_create failed, Possibly out of memory or process/resource limits reached
                        </code>
                    </p>
                    <div className='mt-8 items-center justify-end sm:flex'>
                        <Button onClick={() => setVisible(false)} className='w-full border-transparent sm:w-auto'>
                            Close
                        </Button>
                    </div>
                </>
            )}
        </Modal>
    );
};

export default PIDLimitModalFeature;
