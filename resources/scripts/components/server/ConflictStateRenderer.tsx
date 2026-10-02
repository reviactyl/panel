import { ServerContext } from '@/state/server';
import ScreenBlock from '@/reviactyl/elements/ScreenBlock';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('server/index');
    const status = ServerContext.useStoreState((state) => state.server.data?.status || null);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data?.isTransferring || false);
    const isNodeUnderMaintenance = ServerContext.useStoreState(
        (state) => state.server.data?.isNodeUnderMaintenance || false,
    );

    return status === 'installing' || status === 'install_failed' || status === 'reinstall_failed' ? (
        <ScreenBlock
            title={t('installer-running-title')}
            message={t('installer-running-message')}
        />
    ) : status === 'suspended' ? (
        <ScreenBlock
            title={t('server-suspended-title')}
            message={t('server-suspended-message')}
        />
    ) : isNodeUnderMaintenance ? (
        <ScreenBlock
            title={t('node-maintenance-title')}
            message={t('node-maintenance-message')}
        />
    ) : (
        <ScreenBlock
            title={isTransferring ? t('server-transferring-title') : t('server-restoring-title')}
            message={isTransferring ? t('server-transferring-message') : t('server-restoring-message')}
        />
    );
};
