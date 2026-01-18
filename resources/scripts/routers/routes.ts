import React, { lazy } from 'react';
import ServerConsole from '@/components/server/console/ServerConsoleContainer';
import DatabasesContainer from '@/components/server/databases/DatabasesContainer';
import ScheduleContainer from '@/components/server/schedules/ScheduleContainer';
import UsersContainer from '@/components/server/users/UsersContainer';
import BackupContainer from '@/components/server/backups/BackupContainer';
import NetworkContainer from '@/components/server/network/NetworkContainer';
import StartupContainer from '@/components/server/startup/StartupContainer';
import FileManagerContainer from '@/components/server/files/FileManagerContainer';
import SettingsContainer from '@/components/server/settings/SettingsContainer';
import AccountOverviewContainer from '@/components/dashboard/AccountOverviewContainer';
import AccountApiContainer from '@/components/dashboard/AccountApiContainer';
import AccountSSHContainer from '@/components/dashboard/ssh/AccountSSHContainer';
import ActivityLogContainer from '@/components/dashboard/activity/ActivityLogContainer';
import ServerActivityLogContainer from '@/components/server/ServerActivityLogContainer';
import { FaBoltLightning, FaBoxArchive, FaCalendar, FaChartLine, FaDatabase, FaEye, FaFolder, FaGear, FaKey, FaLock, FaPlay, FaTerminal, FaUser, FaUsers } from 'react-icons/fa6';

// Each of the router files is already code split out appropriately — so
// all of the items above will only be loaded in when that router is loaded.
//
// These specific lazy loaded routes are to avoid loading in heavy screens
// for the server dashboard when they're only needed for specific instances.
const FileEditContainer = lazy(() => import('@/components/server/files/FileEditContainer'));
const ScheduleEditContainer = lazy(() => import('@/components/server/schedules/ScheduleEditContainer'));
const HistoricalGraphsContainer = lazy(() => import('@/components/server/metrics/HistoricalGraphsContainer'));

interface RouteDefinition {
    path: string;
    // If undefined is passed this route is still rendered into the router itself
    // but no navigation link is displayed in the sub-navigation menu.
    name: string | undefined;
    component: React.ComponentType;
    exact?: boolean;
    icon?: React.ComponentType<React.SVGProps<SVGSVGElement>>;
}

interface ServerRouteDefinition extends RouteDefinition {
    permission: string | string[] | null;
    nestId?: number;
    eggId?: number;
    nestIds?: number[];
    eggIds?: number[];
}

interface Routes {
    // All of the routes available under "/account"
    account: RouteDefinition[];
    // All of the routes available under "/server/:id"
    server: {
        control: ServerRouteDefinition[];
        management: ServerRouteDefinition[];
        administration: ServerRouteDefinition[];
    };
}

export default {
    account: [
        {
            path: '/',
            name: 'account.overview',
            component: AccountOverviewContainer,
            icon: FaUser,
            exact: true,
        },
        {
            path: '/api',
            name: 'account.api',
            icon: FaLock,
            component: AccountApiContainer,
        },
        {
            path: '/ssh',
            name: 'account.ssh',
            icon: FaKey,
            component: AccountSSHContainer,
        },
        {
            path: '/activity',
            name: 'account.activity',
            icon: FaEye,
            component: ActivityLogContainer,
        },
    ],
    server: {
        control: [
            {
                path: '/',
                permission: null,
                name: 'server.console',
                component: ServerConsole,
                icon: FaTerminal,
                exact: true,
            },
            {
                path: '/files',
                permission: 'file.*',
                name: 'server.files',
                component: FileManagerContainer,
                icon: FaFolder,
            },
            {
                path: '/files/:action(edit|new)',
                permission: 'file.*',
                name: undefined,
                component: FileEditContainer,
            },
            {
                path: '/startup',
                permission: 'startup.*',
                name: 'server.startup',
                component: StartupContainer,
                icon: FaPlay,
            },
            {
                path: '/network',
                permission: 'allocation.*',
                name: 'server.network',
                component: NetworkContainer,
                icon: FaBoltLightning,
            },
            {
                path: '/metrics',
                permission: null,
                name: 'server.metrics',
                component: HistoricalGraphsContainer,
                icon: FaChartLine,
            },
        ],
        management: [
            {
                path: '/databases',
                permission: 'database.*',
                name: 'server.databases',
                component: DatabasesContainer,
                icon: FaDatabase,
            },
            {
                path: '/schedules',
                permission: 'schedule.*',
                name: 'server.schedules',
                component: ScheduleContainer,
                icon: FaCalendar,
            },
            {
                path: '/schedules/:id',
                permission: 'schedule.*',
                name: undefined,
                component: ScheduleEditContainer,
            },
            {
                path: '/backups',
                permission: 'backup.*',
                name: 'server.backups',
                component: BackupContainer,
                icon: FaBoxArchive,
            },
        ],
        administration: [
            {
                path: '/users',
                permission: 'user.*',
                name: 'server.users',
                component: UsersContainer,
                icon: FaUsers,
            },
            {
                path: '/settings',
                permission: ['settings.*', 'file.sftp'],
                name: 'server.settings',
                component: SettingsContainer,
                icon: FaGear,
            },
            {
                path: '/activity',
                permission: 'activity.*',
                name: 'server.activity',
                component: ServerActivityLogContainer,
                icon: FaEye,
            },
        ],
    },
} as Routes;
