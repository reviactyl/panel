import { ServerContext } from '@/state/server';
import ScreenBlock from '@/reviactyl/elements/ScreenBlock';
import ServerInstallSvg from '@/assets/images/server_installing.svg';
import ServerErrorSvg from '@/assets/images/server_error.svg';
import ServerRestoreSvg from '@/assets/images/server_restore.svg';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('server/index');
    const status = ServerContext.useStoreState((state) => state.server.data?.status || null);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data?.isTransferring || false);
    const isNodeUnderMaintenance = ServerContext.useStoreState(
        (state) => state.server.data?.isNodeUnderMaintenance || false
    );

    return status === 'installing' || status === 'install_failed' || status === 'reinstall_failed' ? (
        <ScreenBlock
            title={t('installer-running-title')}
            image={ServerInstallSvg}
            message={t('installer-running-message')}
        />
    ) : status === 'suspended' ? (
        <ScreenBlock
            title={t('server-suspended-title')}
            image={ServerErrorSvg}
            message={t('server-suspended-message')}
        />
    ) : isNodeUnderMaintenance ? (
        <ScreenBlock
            title={t('node-maintenance-title')}
            image={ServerErrorSvg}
            message={t('node-maintenance-message')}
        />
    ) : (
        <ScreenBlock
            title={isTransferring ? t('server-transferring-title') : t('server-restoring-title')}
            image={ServerRestoreSvg}
            message={isTransferring ? t('server-transferring-message') : t('server-restoring-message')}
        />
    );
};
