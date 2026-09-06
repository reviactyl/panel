import { useContext, useEffect, useState } from 'react';
import Spinner from '@/reviactyl/elements/Spinner';
import useFlash from '@/plugins/useFlash';
import Can from '@/reviactyl/elements/Can';
import CreateBackupButton from '@/components/server/backups/CreateBackupButton';
import FlashMessageRender from '@/components/FlashMessageRender';
import BackupRow from '@/components/server/backups/BackupRow';
import getServerBackups, { Context as ServerBackupContext } from '@/api/swr/getServerBackups';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/reviactyl/elements/ServerContentBlock';
import Pagination from '@/reviactyl/elements/Pagination';
import Card from '@/reviactyl/ui/Card';
import { ArchiveIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';
import { ExtensionSlot } from '@/extensions/ExtensionSlot';

const BackupContainer = () => {
    const { t } = useTranslation('server/backups');
    const { page, setPage } = useContext(ServerBackupContext);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { data: backups, error, isValidating } = getServerBackups();

    const backupLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.backups);

    useEffect(() => {
        if (!error) {
            clearFlashes('backups');

            return;
        }

        clearAndAddHttpError({ error, key: 'backups' });
    }, [error]);

    if (!backups || (error && isValidating)) {
        return <Spinner size={'large'} centered />;
    }

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey={'backups'} className='mb-4' />
            <ExtensionSlot name='server:backups:above' />
            <Pagination data={backups} onPageSelect={setPage}>
                {({ items }) =>
                    !items.length ? (
                        // Don't show any error messages if the server has no backups and the user cannot
                        // create additional ones for the server.
                        !backupLimit ? null : (
                            <p className='text-center text-sm text-gray-300'>
                                {page > 1 ? t('out-of-backups') : t('no-backups')}
                            </p>
                        )
                    ) : (
                        items.map((backup, index) => (
                            <BackupRow key={backup.uuid} backup={backup} className={index > 0 ? 'mt-2' : undefined} />
                        ))
                    )
                }
            </Pagination>
            {backupLimit === 0 && (
                <Card>
                    <p className='flex justify-center text-center text-sm text-gray-400'>
                        <ArchiveIcon className='w-5 h-5 mr-1' />
                        {t('cannot-create')}
                    </p>
                </Card>
            )}
            <Can action={'backup.create'}>
                <div className='mt-6 sm:flex items-center justify-end'>
                    {backupLimit > 0 && backups.backupCount > 0 && (
                        <p className='text-sm text-gray-300 mb-4 sm:mr-6 sm:mb-0'>
                            {t('created-count', { count: backups.backupCount, limit: backupLimit })}
                        </p>
                    )}
                    {backupLimit > 0 && backupLimit > backups.backupCount && <CreateBackupButton />}
                </div>
            </Can>
            <ExtensionSlot name='server:backups:below' />
        </ServerContentBlock>
    );
};

export default () => {
    const [page, setPage] = useState<number>(1);

    return (
        <ServerBackupContext.Provider value={{ page, setPage }}>
            <BackupContainer />
        </ServerBackupContext.Provider>
    );
};
