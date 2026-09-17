import { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import Modal from '@/reviactyl/elements/Modal';
import Button from '@/reviactyl/elements/Button';
import FlashMessageRender from '@/components/FlashMessageRender';
import useFlash from '@/plugins/useFlash';
import { SocketEvent } from '@/components/server/events';
import { useStoreState } from 'easy-peasy';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';

const SteamDiskSpaceFeature = () => {
    const [visible, setVisible] = useState(false);
    const [loading] = useState(false);

    const status = ServerContext.useStoreState((state) => state.status.value);
    const { clearFlashes } = useFlash();
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);
    const accountAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const { session } = useSubuserPreview();
    const isAdmin = accountAdmin && !session;

    useEffect(() => {
        if (!connected || !instance || status === 'running') return;

        const errors = ['steamcmd needs 250mb of free disk space to update', '0x202 after update job'];

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
        clearFlashes('feature:steamDiskSpace');
    }, []);

    return (
        <Modal
            visible={visible}
            onDismissed={() => setVisible(false)}
            closeOnBackground={false}
            showSpinnerOverlay={loading}
        >
            <FlashMessageRender key={'feature:steamDiskSpace'} className='mb-4' />
            {isAdmin ? (
                <>
                    <div className='mt-4 items-center sm:flex'>
                        <h2 className='mb-4 text-2xl text-gray-100'>Out of available disk space...</h2>
                    </div>
                    <p className='mt-4'>
                        This server has run out of available disk space and cannot complete the install or update
                        process.
                    </p>
                    <p className='mt-4'>
                        Ensure the machine has enough disk space by typing{' '}
                        <code className='rounded bg-gray-950 px-2 py-1 font-mono'>df -h</code> on the machine hosting
                        this server. Delete files or increase the available disk space to resolve the issue.
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
                        <h2 className='mb-4 text-2xl text-gray-100'>Out of available disk space...</h2>
                    </div>
                    <p className='mt-4'>
                        This server has run out of available disk space and cannot complete the install or update
                        process. Please get in touch with the administrator(s) and inform them of disk space issues.
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

export default SteamDiskSpaceFeature;
