import React, { useLayoutEffect, useState } from 'react';
import type { Server } from '@/api/server/getServer';
import { createRoot } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import i18next from 'i18next';
import { initReactI18next } from 'react-i18next';
import { ServerContext } from '@/state/server';
import flashes from '@/state/flashes';
import DatabasesContainer from '@/components/server/databases/DatabasesContainer';
import { updateThemeColors } from '@/lib/themeColors';
import '@/assets/tailwind.css';

void i18next.use(initReactI18next).init({
    lng: 'en',
    resources: {
        en: { 'server/databases': { created: '{{count}} of {{limit}} databases', 'new-database': 'New Database' } },
    },
    interpolation: { escapeValue: false },
});
const parameters = new URLSearchParams(location.search);
const store = createStore({
    flashes,
    user: { data: undefined },
    settings: { data: { name: 'Panel' } },
    designify: { data: { customCopyright: false } },
});
const Surface = () => {
    const [ready, setReady] = useState(false);
    const actions = ServerContext.useStoreActions((actions) => actions.server);
    useLayoutEffect(() => {
        actions.setServer({ uuid: 'server-uuid', name: 'Server', featureLimits: { databases: 5 } } as Server);
        actions.setPermissions(parameters.has('restricted') ? ['database.read'] : ['*']);
        setReady(true);
    }, []);
    return ready ? <DatabasesContainer /> : null;
};
updateThemeColors();
createRoot(document.getElementById('root')!).render(
    <StoreProvider store={store}>
        <ServerContext.Provider>
            <Surface />
        </ServerContext.Provider>
    </StoreProvider>,
);
