import React from 'react';
import { createRoot } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import user from '@/state/user';
import LanguageSwitcher from '@/reviactyl/ui/LanguageSwitcher';
import FileEditorSwitcher from '@/reviactyl/ui/FileEditorSwitcher';

const store = createStore({ user, settings: { data: { locale: 'en' } } });
store.getActions().user.updateUserData({ language: 'en', fileEditor: 'cm' });
window.fetch = async (input) =>
    new Response(
        JSON.stringify(
            String(input).includes('locales')
                ? { en: { name: 'English' }, de: { name: 'Deutsch' } }
                : { cm: { display: 'CodeMirror' }, mo: { display: 'Monaco' } },
        ),
    );
createRoot(document.getElementById('root')!).render(
    <StoreProvider store={store}>
        <LanguageSwitcher />
        <FileEditorSwitcher />
    </StoreProvider>,
);
