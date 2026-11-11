import { useEffect, useRef, useState } from 'react';
import { Websocket } from '@/plugins/Websocket';
import { ServerContext } from '@/state/server';
import getWebsocketToken from '@/api/server/getWebsocketToken';
import { motion } from 'framer-motion';
import Spinner from '@/reviactyl/elements/Spinner';
import Button from '@/reviactyl/elements/Button';
import { useTranslation } from 'react-i18next';
import { FaTriangleExclamation } from 'react-icons/fa6';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';
import { PreviewWebsocket } from '@/plugins/PreviewWebsocket';

const reconnectErrors = ['jwt: exp claim is invalid', 'jwt: created too far in past (denylist)'];

export default () => {
    const { t } = useTranslation('server/console');
    const { session } = useSubuserPreview();
    let updatingToken = false;
    const acquiringToken = useRef<string | null>(null);
    const connectionGeneration = useRef(0);
    const [error, setError] = useState<'connecting' | string>('');
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);
    const uuid = ServerContext.useStoreState((state) => state.server.data?.uuid);
    const setServerStatus = ServerContext.useStoreActions((actions) => actions.status.setServerStatus);
    const { setInstance, setConnectionState } = ServerContext.useStoreActions((actions) => actions.socket);

    const updateToken = (uuid: string, socket: Websocket) => {
        if (updatingToken) return;

        updatingToken = true;
        getWebsocketToken(uuid)
            .then((data) => socket.setToken(data.token, true))
            .catch((error) => console.error(error))
            .then(() => {
                updatingToken = false;
            });
    };

    const connect = (uuid: string) => {
        if (acquiringToken.current === uuid) return;
        acquiringToken.current = uuid;
        const generation = connectionGeneration.current;

        const socket = session ? new PreviewWebsocket(uuid) : new Websocket();

        socket.on('auth success', () => setConnectionState(true));
        socket.on('SOCKET_CLOSE', () => setConnectionState(false));
        socket.on('SOCKET_CONNECT_ERROR', () => {
            setError('Failed to connect to websocket instance after multiple attempts: try refreshing the page.');
        });
        socket.on('SOCKET_ERROR', () => {
            setError('connecting');
            setConnectionState(false);
        });
        socket.on('status', (status) => setServerStatus(status));

        socket.on('daemon error', (message) => {
            console.warn('Got error message from daemon socket:', message);
        });

        socket.on('token expiring', () => updateToken(uuid, socket));
        socket.on('token expired', () => updateToken(uuid, socket));
        socket.on('jwt error', (error: string) => {
            setConnectionState(false);
            console.warn('JWT validation error from agent:', error);

            if (reconnectErrors.find((v) => error.toLowerCase().indexOf(v) >= 0)) {
                updateToken(uuid, socket);
            } else {
                setError(
                    'There was an error validating the credentials provided for the websocket. Please refresh the page.',
                );
            }
        });

        socket.on('transfer status', (status: string) => {
            if (status === 'starting' || status === 'success') {
                return;
            }

            // This code forces a reconnection to the websocket which will connect us to the target node instead of the source node
            // in order to be able to receive transfer logs from the target node.
            socket.close();
            setError('connecting');
            setConnectionState(false);
            setInstance(null);
            connect(uuid);
        });

        getWebsocketToken(uuid)
            .then((data) => {
                if (generation !== connectionGeneration.current) return;
                // Connect and then set the authentication token.
                socket.setToken(data.token).connect(data.socket);

                // Once that is done, set the instance.
                setInstance(socket);
            })
            .catch((error) => {
                if (generation !== connectionGeneration.current) return;
                console.error(error);
                setError('token');
                setConnectionState(false);
            })
            .finally(() => {
                if (generation === connectionGeneration.current && acquiringToken.current === uuid) {
                    acquiringToken.current = null;
                }
            });
    };

    useEffect(() => {
        void (connected && setError(''));
    }, [connected]);

    useEffect(() => {
        return () => {
            void (instance && instance.close());
        };
    }, [instance]);

    useEffect(() => {
        setError('');
        // If there is already an instance or there is no server, just exit out of this process
        // since we don't need to make a new connection.
        if (!instance && uuid) {
            connect(uuid);
        }

        return () => {
            connectionGeneration.current += 1;
            acquiringToken.current = null;
        };
    }, [uuid, session?.uuid]);

    return error ? (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.15, ease: 'easeIn' }}
            role={error === 'connecting' ? 'status' : 'alert'}
            className='rounded-ui border border-danger/80 bg-danger/40 backdrop-blur-sm px-4 py-2 shadow-lg mt-2 mx-2 -mb-1'
        >
            <div className='flex flex-wrap items-center justify-center gap-y-2'>
                {error === 'connecting' ? (
                    <>
                        <Spinner className='not-lg:h-3 not-lg:w-4' size={'small'} />
                        <p className='ml-2 text-sm text-danger-text'>{t('connection-trouble')}</p>
                    </>
                ) : (
                    <>
                        <FaTriangleExclamation className='text-red-700 dark:text-red-400' />
                        <p className='ml-2 text-sm text-gray-100'>
                            {error === 'token' ? t('connection-error') : error}
                        </p>
                        {error === 'token' && uuid && (
                            <Button
                                type='button'
                                size='xsmall'
                                className='ml-3'
                                onClick={() => {
                                    setError('connecting');
                                    connect(uuid);
                                }}
                            >
                                {t('retry')}
                            </Button>
                        )}
                    </>
                )}
            </div>
        </motion.div>
    ) : null;
};
