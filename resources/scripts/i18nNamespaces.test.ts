import { describe, expect, it } from 'vitest';
import { namespacesForPath } from './i18nNamespaces';

describe('namespaces needed before a page renders', () => {
    it.each([
        ['/auth/login', ['auth', 'dashboard/account', 'server/users']],
        ['/', ['dashboard/account', 'server/users', 'notifications', 'dashboard/index']],
        ['/account/api', ['dashboard/account', 'server/users', 'notifications', 'dashboard/index']],
        [
            '/server/abc',
            ['dashboard/account', 'server/users', 'notifications', 'server/index', 'server/console', 'server/console'],
        ],
        [
            '/server/abc/files',
            [
                'dashboard/account',
                'server/users',
                'notifications',
                'server/index',
                'server/console',
                'server/files',
                'server/recycle',
                'generic',
                'validation',
            ],
        ],
        [
            '/server/abc/unknown',
            ['dashboard/account', 'server/users', 'notifications', 'server/index', 'server/console'],
        ],
    ])('%s', (path, expected) => {
        expect(namespacesForPath(path)).toEqual(expected);
    });
});
