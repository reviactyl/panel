import React, { useEffect, useState } from 'react';
import { Button } from '@/components/elements/button/index';
import Can from '@/components/elements/Can';
import { ServerContext } from '@/state/server';
import { PowerAction } from '@/components/server/console/ServerConsoleContainer';
import { Dialog } from '@/components/elements/dialog';
import { useTranslation } from 'react-i18next';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';

interface PowerButtonProps {
    className?: string;
}

export default ({ className }: PowerButtonProps) => {
    const { t } = useTranslation('server/index');
    const [open, setOpen] = useState(false);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);
    const alwaysShowKillButton = useStoreState((state: ApplicationStore) => state.reviactyl.data?.alwaysShowKillButton);

    const killable = status === 'stopping';
    const onButtonClick = (
        action: PowerAction | 'kill-confirmed',
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>
    ): void => {
        e.preventDefault();
        if (action === 'kill') {
            return setOpen(true);
        }

        if (instance) {
            setOpen(false);
            instance.send('set state', action === 'kill-confirmed' ? 'kill' : action);
        }
    };

    useEffect(() => {
        if (status === 'offline') {
            setOpen(false);
        }
    }, [status]);

    return (
        <div className={className}>
            <Dialog.Confirm
                open={open}
                hideCloseIcon
                onClose={() => setOpen(false)}
                title={'Forcibly Stop Process'}
                confirm={'Continue'}
                onConfirmed={onButtonClick.bind(this, 'kill-confirmed')}
            >
                {t('kill-warning')}
            </Dialog.Confirm>
            <Can action={'control.start'}>
                {(!alwaysShowKillButton || status === 'offline') && (
                <Button.Success
                    className={'flex-1'}
                    onClick={onButtonClick.bind(this, 'start')}
                >
                    {t('start')}
                </Button.Success>
                )}
            </Can>
            <Can action={'control.restart'}>
                {(!alwaysShowKillButton || status !== 'offline') && (
                <Button.Text 
                    className={'flex-1'}
                    disabled={!status}
                    onClick={onButtonClick.bind(this, 'restart')}>
                    {t('restart')}
                </Button.Text>
                )}
            </Can>
            <Can action={'control.stop'}>
                <Button.Danger
                    className={'flex-1'}
                    disabled={status === 'offline'}
                    onClick={onButtonClick.bind(this, !alwaysShowKillButton && killable ? 'kill' : 'stop')}
                >
                    {!alwaysShowKillButton && killable ? t('kill') : t('stop')}
                </Button.Danger>
                {alwaysShowKillButton && (
                <Button.Danger
                    className={'flex-1'}
                    disabled={status === 'offline'}
                    onClick={onButtonClick.bind(this, 'kill')}
                >
                    {t('kill')}
                </Button.Danger>
                )}
            </Can>
        </div>
    );
};
