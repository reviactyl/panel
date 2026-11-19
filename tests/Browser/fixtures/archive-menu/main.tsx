import React, { useLayoutEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { StoreProvider } from 'easy-peasy';
import { MemoryRouter } from 'react-router-dom';
import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import { store } from '@/state';
import { ServerContext } from '@/state/server';
import MassActionsBar from '@/components/server/files/MassActionsBar';
import '@/assets/tailwind.css';

void i18n.use(initReactI18next).init({
    lng: 'en',
    fallbackLng: 'en',
    resources: {
        en: {
            'server/files': {
                archive: 'Archive',
                'archive-as': 'Archive as {{format}}',
                delete: 'Delete',
                move: 'Move',
                'mass-actions': {
                    archiving: 'Archiving...',
                    deleting: 'Deleting...',
                },
            },
            generic: {
                cancel: 'Cancel',
            },
        },
    },
});

store.getActions().user.setUserData({
    uuid: 'user-uuid',
    username: 'demo',
    email: 'demo@example.com',
    language: 'en',
    rootAdmin: false,
    useTotp: false,
    createdAt: new Date(),
    fileEditor: 'codemirror',
    archiveFormat: 'tar.gz',
});

function Bootstrap() {
    const setServer = ServerContext.useStoreActions((actions) => actions.server.setServer);
    const setPermissions = ServerContext.useStoreActions((actions) => actions.server.setPermissions);
    const selectedFiles = ServerContext.useStoreState((state) => state.files.selectedFiles);
    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);

    useLayoutEffect(() => {
        setServer({
            id: 'server',
            internalId: 1,
            uuid: 'uuid',
            name: 'Demo',
            node: 'node',
            isNodeUnderMaintenance: false,
            status: null,
            sftpDetails: { ip: '127.0.0.1', port: 2022 },
            invocation: '',
            dockerImage: '',
            description: '',
            limits: { memory: 0, swap: 0, disk: 0, io: 0, cpu: 0, threads: null, oomDisabled: false },
            eggFeatures: [],
            featureLimits: { databases: 0, allocations: 0, backups: 0 },
            isTransferring: false,
            variables: [],
            allocations: [],
            nestId: 1,
            eggId: 1,
        });
        setPermissions(['*']);
        setSelectedFiles(['welcome.txt']);
    }, [setPermissions, setSelectedFiles, setServer]);

    return (
        <>
            <div style={{ height: 800 }} />
            <div className='flex items-center gap-2' style={{ marginLeft: '60%' }}>
                {selectedFiles.length > 0 ? (
                    <MassActionsBar />
                ) : (
                    <button type='button' onClick={() => setSelectedFiles(['welcome.txt'])}>
                        Reselect
                    </button>
                )}
            </div>
            <div className='relative z-30'>
                File rows <input type='checkbox' />
            </div>
            <div style={{ height: 1000 }} />
            <nav
                data-mobile-bottom-bar
                className='fixed bottom-0 inset-x-0 z-50 bg-gray-800 lg:hidden'
                style={{ height: 73 }}
            >
                <button style={{ position: 'relative', top: -20 }}>Menu</button>
            </nav>
        </>
    );
}

createRoot(document.getElementById('root')!).render(
    <StoreProvider store={store}>
        <ServerContext.Provider>
            <MemoryRouter>
                <Bootstrap />
            </MemoryRouter>
        </ServerContext.Provider>
    </StoreProvider>,
);
