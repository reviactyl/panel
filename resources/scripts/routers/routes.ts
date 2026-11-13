import type { ComponentType } from 'react';
import preloadable from '@/plugins/preloadable';
import i18n from '@/i18n';
import {
    FaBoltLightning,
    FaBoxArchive,
    FaCalendar,
    FaChartLine,
    FaDatabase,
    FaEye,
    FaFolder,
    FaGear,
    FaKey,
    FaLock,
    FaPlay,
    FaFingerprint,
    FaTerminal,
    FaUser,
    FaUsers,
} from 'react-icons/fa6';

const screen = (load: () => Promise<{ default: ComponentType }>, namespaces: string[]) =>
    preloadable(() => Promise.all([load(), i18n.loadNamespaces(namespaces)]).then(([module]) => module));

const ServerConsole = screen(() => import('@/components/server/console/ServerConsoleContainer'), ['server/console']);
const DatabasesContainer = screen(
    () => import('@/components/server/databases/DatabasesContainer'),
    ['server/databases', 'strings', 'generic'],
);
const ScheduleContainer = screen(
    () => import('@/components/server/schedules/ScheduleContainer'),
    ['server/schedules', 'strings'],
);
const UsersContainer = screen(() => import('@/components/server/users/UsersContainer'), ['server/users', 'strings']);
const BackupContainer = screen(
    () => import('@/components/server/backups/BackupContainer'),
    ['server/backups', 'strings'],
);
const NetworkContainer = screen(
    () => import('@/components/server/network/NetworkContainer'),
    ['server/network', 'strings'],
);
const StartupContainer = screen(
    () => import('@/components/server/startup/StartupContainer'),
    ['server/startup', 'server/recycle', 'strings'],
);
const FileManagerContainer = screen(
    () => import('@/components/server/files/FileManagerContainer'),
    ['server/files', 'server/recycle', 'strings', 'generic', 'validation'],
);
const SettingsContainer = screen(
    () => import('@/components/server/settings/SettingsContainer'),
    ['server/settings', 'strings'],
);
const AccountOverviewContainer = screen(
    () => import('@/components/dashboard/AccountOverviewContainer'),
    ['dashboard/account', 'strings'],
);
const AccountApiContainer = screen(
    () => import('@/components/dashboard/AccountApiContainer'),
    ['dashboard/account', 'strings'],
);
const AccountSSHContainer = screen(
    () => import('@/components/dashboard/ssh/AccountSSHContainer'),
    ['dashboard/account', 'strings'],
);
const ActivityLogContainer = screen(
    () => import('@/components/dashboard/activity/ActivityLogContainer'),
    ['dashboard/account', 'strings', 'activity'],
);
const PasskeysContainer = screen(
    () => import('@/components/dashboard/PasskeysContainer'),
    ['dashboard/account', 'strings'],
);
const ServerActivityLogContainer = screen(
    () => import('@/components/server/ServerActivityLogContainer'),
    ['server/activity', 'activity'],
);
const FileEditContainer = screen(
    () => import('@/components/server/files/FileEditContainer'),
    ['server/files', 'server/recycle', 'strings', 'validation'],
);
const ScheduleEditContainer = screen(
    () => import('@/components/server/schedules/ScheduleEditContainer'),
    ['server/schedules'],
);
const HistoricalGraphsContainer = screen(
    () => import('@/components/server/metrics/HistoricalGraphsContainer'),
    ['server/metrics'],
);

interface RouteDefinition {
    route: string;
    path?: string;
    // If undefined is passed this route is still rendered into the router itself
    // but no navigation link is displayed in the sub-navigation menu.
    name: string | undefined;
    component: ComponentType & { preload?: () => Promise<unknown> };
    end?: boolean;
    icon?: ComponentType<React.SVGProps<SVGSVGElement>>;
}

interface ServerRouteDefinition extends RouteDefinition {
    permission?: string | string[];
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
            route: '',
            path: '',
            name: 'account.overview',
            component: AccountOverviewContainer,
            icon: FaUser,
            end: true,
        },
        {
            route: 'api',
            name: 'account.api',
            icon: FaLock,
            component: AccountApiContainer,
        },
        {
            route: 'passkeys',
            name: 'account.passkeys',
            icon: FaFingerprint,
            component: PasskeysContainer,
        },
        {
            route: 'ssh',
            name: 'account.ssh',
            icon: FaKey,
            component: AccountSSHContainer,
        },
        {
            route: 'activity',
            name: 'account.activity',
            icon: FaEye,
            component: ActivityLogContainer,
        },
    ],
    server: {
        control: [
            {
                route: '',
                path: '',
                permission: null,
                name: 'server.console',
                component: ServerConsole,
                icon: FaTerminal,
                end: true,
            },
            {
                route: 'files/*',
                permission: 'file.*',
                name: 'server.files',
                component: FileManagerContainer,
                icon: FaFolder,
            },
            {
                route: 'files/edit/*',
                permission: 'file.*',
                name: undefined,
                component: FileEditContainer,
            },
            {
                route: 'files/new/*',
                permission: 'file.*',
                name: undefined,
                component: FileEditContainer,
            },
            {
                route: 'startup/*',
                permission: 'startup.*',
                name: 'server.startup',
                component: StartupContainer,
                icon: FaPlay,
            },
            {
                route: 'network/*',
                permission: 'allocation.*',
                name: 'server.network',
                component: NetworkContainer,
                icon: FaBoltLightning,
            },
            {
                route: 'metrics/*',
                permission: null,
                name: 'server.metrics',
                component: HistoricalGraphsContainer,
                icon: FaChartLine,
            },
        ],
        management: [
            {
                route: 'databases/*',
                permission: 'database.*',
                name: 'server.databases',
                component: DatabasesContainer,
                icon: FaDatabase,
            },
            {
                route: 'schedules/*',
                permission: 'schedule.*',
                name: 'server.schedules',
                component: ScheduleContainer,
                icon: FaCalendar,
            },
            {
                route: 'schedules/:id/*',
                permission: 'schedule.*',
                name: undefined,
                component: ScheduleEditContainer,
            },
            {
                route: 'backups/*',
                permission: 'backup.*',
                name: 'server.backups',
                component: BackupContainer,
                icon: FaBoxArchive,
            },
        ],
        administration: [
            {
                route: 'users/*',
                permission: 'user.*',
                name: 'server.users',
                component: UsersContainer,
                icon: FaUsers,
            },
            {
                route: 'settings/*',
                permission: ['settings.*', 'file.sftp'],
                name: 'server.settings',
                component: SettingsContainer,
                icon: FaGear,
            },
            {
                route: 'activity',
                permission: 'activity.*',
                name: 'server.activity',
                component: ServerActivityLogContainer,
                icon: FaEye,
            },
        ],
    },
} as Routes;
