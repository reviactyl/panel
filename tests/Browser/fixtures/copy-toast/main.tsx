import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { MemoryRouter } from 'react-router-dom';
import i18next from 'i18next';
import { I18nextProvider } from 'react-i18next';
import { SubuserPreviewProvider } from '@/context/SubuserPreviewContext';
import CopyOnClick from '@/reviactyl/elements/CopyOnClick';
import MobileBottomBar from '@/reviactyl/ui/MobileBottomBar';
import '@/assets/tailwind.css';

const i18n = i18next.createInstance();
void i18n.init({
    lng: 'en',
    resources: {
        en: {
            'dashboard/account': {
                'mobile-navigation': {
                    navigation: 'Mobile navigation',
                    home: 'Home',
                    account: 'Account',
                    menu: 'Menu',
                    close: 'Close',
                    'show-sidebar': 'Show sidebar',
                    'close-sidebar': 'Close sidebar',
                },
            },
        },
    },
});
const store = createStore({ user: { data: null } });
const App = () => {
    const [open, setOpen] = useState(false);
    return (
        <StoreProvider store={store}>
            <SubuserPreviewProvider>
                <I18nextProvider i18n={i18n}>
                    <MemoryRouter>
                        <main className='p-8'>
                            <CopyOnClick text={0}>
                                <p>Copy zero</p>
                            </CopyOnClick>
                            <CopyOnClick text={'x'.repeat(300)}>
                                <p>Copy long value</p>
                            </CopyOnClick>
                            <CopyOnClick text='secret' showInNotification={false}>
                                <p>Copy secret</p>
                            </CopyOnClick>
                            <MobileBottomBar sidebarOpen={open} onShowSidebar={() => setOpen(!open)} />
                        </main>
                    </MemoryRouter>
                </I18nextProvider>
            </SubuserPreviewProvider>
        </StoreProvider>
    );
};
createRoot(document.getElementById('root')!).render(<App />);
