import { useEffect, useState } from 'react';
import { ActivityLogFilters, useActivityLogs } from '@/api/account/activity';
import { useFlashKey } from '@/plugins/useFlash';
import FlashMessageRender from '@/components/FlashMessageRender';
import { Link } from 'react-router-dom';
import PaginationFooter from '@/reviactyl/elements/table/PaginationFooter';
import { ClockIcon, DesktopComputerIcon, XCircleIcon } from '@heroicons/react/solid';
import Spinner from '@/reviactyl/elements/Spinner';
import { styles as btnStyles } from '@/reviactyl/components/button/index';
import classNames from 'classnames';
import ActivityLogEntry from '@/reviactyl/elements/activity/ActivityLogEntry';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import useLocationHash from '@/plugins/useLocationHash';
import AccountContentBlock from '@/components/dashboard/AccountContentBlock';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('dashboard/account');
    const { hash } = useLocationHash();
    const { clearAndAddHttpError } = useFlashKey('account');
    const [filters, setFilters] = useState<ActivityLogFilters>({ page: 1, sorts: { timestamp: -1 } });
    const { data, isValidating, error } = useActivityLogs(filters, {
        revalidateOnMount: true,
        revalidateOnFocus: false,
    });

    useEffect(() => {
        setFilters((value) => ({ ...value, page: 1, filters: { ip: hash.ip, event: hash.event } }));
    }, [hash]);

    useEffect(() => {
        clearAndAddHttpError(error);
    }, [error]);

    const isEmptyPage = !!data && !data.items.length && data.pagination.total > 0;

    useEffect(() => {
        if (isEmptyPage && filters.page !== 1) {
            setFilters((value) => ({ ...value, page: 1 }));
        }
    }, [isEmptyPage, filters.page]);

    const hasFilters = !!(filters.filters?.event || filters.filters?.ip);

    return (
        <AccountContentBlock title={t('activity.title')}>
            <FlashMessageRender byKey={'account'} />
            {hasFilters && (
                <div className={'mb-4 flex justify-end'}>
                    <Link
                        to={'#'}
                        className={classNames(btnStyles.button, btnStyles.text, 'w-full sm:w-auto')}
                        onClick={() => setFilters((value) => ({ ...value, page: 1, filters: {} }))}
                    >
                        {t('activity.clear-filters')} <XCircleIcon className={'w-4 h-4 ml-2'} />
                    </Link>
                </div>
            )}
            {(!data && isValidating) || (isEmptyPage && filters.page !== 1) ? (
                <div className={'py-16'}>
                    <Spinner centered />
                </div>
            ) : data && !data.items.length ? (
                <div className={'rounded-ui border border-gray-800 bg-gray-900 px-6 py-16 text-center'}>
                    <ClockIcon className={'mx-auto mb-4 h-8 w-8 text-gray-400'} aria-hidden />
                    <p className={'text-sm text-gray-300'}>
                        {t(hasFilters ? 'activity.no-matches' : 'activity.no-logs')}
                    </p>
                </div>
            ) : data ? (
                <div className={'bg-gray-900 border border-gray-800 rounded-ui'}>
                    {data?.items.map((activity) => (
                        <ActivityLogEntry key={activity.id} activity={activity}>
                            {typeof activity.properties.useragent === 'string' && (
                                <Tooltip content={activity.properties.useragent} placement={'top'}>
                                    <span>
                                        <DesktopComputerIcon />
                                    </span>
                                </Tooltip>
                            )}
                        </ActivityLogEntry>
                    ))}
                </div>
            ) : null}
            {data && (
                <PaginationFooter
                    className={'mt-4 flex-wrap gap-3'}
                    pagination={data.pagination}
                    onPageSelect={(page) => setFilters((value) => ({ ...value, page }))}
                />
            )}
        </AccountContentBlock>
    );
};
