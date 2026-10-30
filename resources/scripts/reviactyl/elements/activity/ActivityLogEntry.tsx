import React from 'react';
import { Link } from 'react-router-dom';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import Translate from '@/reviactyl/elements/Translate';
import { format, formatDistanceToNowStrict } from 'date-fns';
import { ActivityLog } from '@definitions/user';
import ActivityLogMetaButton from '@/reviactyl/elements/activity/ActivityLogMetaButton';
import { FolderOpenIcon, TerminalIcon } from '@heroicons/react/solid';
import classNames from 'classnames';
import style from './style.module.css';
import Avatar from '@/reviactyl/ui/Avatar';
import useLocationHash from '@/plugins/useLocationHash';
import { getObjectKeys, isObject } from '@/lib/objects';
import { useTranslation } from 'react-i18next';
import Blur from '@/reviactyl/ui/Blur';

interface Props {
    activity: ActivityLog;
    children?: React.ReactNode;
}

function wrapProperties(value: unknown): any {
    if (value === null || typeof value === 'string' || typeof value === 'number') {
        return `<strong>${String(value)}</strong>`;
    }

    if (isObject(value)) {
        return getObjectKeys(value).reduce(
            (obj, key) => {
                if (key === 'count' || (typeof key === 'string' && key.endsWith('_count'))) {
                    return { ...obj, [key]: value[key] };
                }
                return { ...obj, [key]: wrapProperties(value[key]) };
            },
            {} as Record<string, unknown>,
        );
    }

    if (Array.isArray(value)) {
        return value.map(wrapProperties);
    }

    return value;
}

export default ({ activity, children }: Props) => {
    const { t } = useTranslation('activity');
    const { pathTo } = useLocationHash();
    const actor = activity.relationships.actor;
    const properties = wrapProperties(activity.properties);

    return (
        <div className={'group flex gap-3 border-b border-gray-800 px-4 py-5 last:border-0 sm:gap-4 sm:px-5'}>
            <div className={'h-9 w-9 shrink-0 overflow-hidden rounded-full bg-gray-700 select-none sm:h-10 sm:w-10'}>
                <Avatar
                    className={'h-full w-full object-cover'}
                    email={actor?.email || (actor?.uuid === 'system' ? 'system' : undefined)}
                    uuid={actor?.uuid}
                    src={actor?.image}
                    avatarStyle={actor?.uuid === 'system' ? 'gravatar' : undefined}
                />
            </div>
            <div className={'min-w-0 flex-1'}>
                <div className={'flex flex-wrap items-center gap-x-2 gap-y-1'}>
                    <Link
                        to={`#${pathTo({ event: activity.event })}`}
                        className={classNames(
                            style.description,
                            'min-w-0 text-sm font-medium text-gray-50 hover:text-cyan-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-cyan-400',
                        )}
                    >
                        <Translate ns={'activity'} values={properties} i18nKey={activity.event.replace(':', '.')} />
                    </Link>
                    <div className={classNames(style.icons, 'group-hover:text-gray-300')}>
                        {activity.isApi && (
                            <Tooltip placement={'top'} content={t('entries.using-api-key') as string}>
                                <TerminalIcon />
                            </Tooltip>
                        )}
                        {activity.event.startsWith('server:sftp.') && (
                            <Tooltip placement={'top'} content={t('entries.using-sftp') as string}>
                                <FolderOpenIcon />
                            </Tooltip>
                        )}
                        {children}
                    </div>
                </div>
                <div className={'mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-300'}>
                    <Tooltip placement={'top'} content={actor?.email || (t('entries.system-user') as string)}>
                        <span className={'break-all font-medium'}>{actor?.username || t('entries.system')}</span>
                    </Tooltip>
                    {activity.ip && (
                        <span className={'min-w-0 max-w-full font-mono'}>
                            <Blur>{activity.ip}</Blur>
                        </span>
                    )}
                    <Tooltip placement={'top'} content={format(activity.timestamp, 'MMM do, yyyy H:mm:ss')}>
                        <time dateTime={activity.timestamp.toISOString()}>
                            {formatDistanceToNowStrict(activity.timestamp, { addSuffix: true })}
                        </time>
                    </Tooltip>
                </div>
                <p className={'mt-2 break-all font-mono text-xs text-gray-400'}>{activity.event}</p>
            </div>
            {activity.hasAdditionalMetadata && <ActivityLogMetaButton meta={activity.properties} />}
        </div>
    );
};
