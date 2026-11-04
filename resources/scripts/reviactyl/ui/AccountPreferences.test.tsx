import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { createInstance } from 'i18next';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import user, { type UserData } from '@/state/user';
import i18n from '@/i18n';
import updateAccountLanguage from '@/api/account/updateAccountLanguage';
import updateEditor from '@/api/account/updateAccountEditor';
import LanguageSwitcher, { LocaleLoader } from './LanguageSwitcher';
import FileEditorSwitcher from './FileEditorSwitcher';
import NavbarLanguageSwitcher from './NavbarLanguageSwitcher';

vi.mock('@/i18n', async () => ({ default: createInstance() }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/api/account/updateAccountLanguage', () => ({ default: vi.fn() }));
vi.mock('@/api/account/updateAccountEditor', () => ({ default: vi.fn() }));

const initialUser: UserData = {
    uuid: 'user-uuid',
    username: 'demo',
    name_first: 'Demo',
    name_last: 'User',
    email: 'demo@example.com',
    language: 'en',
    fileEditor: 'cm',
    avatarStyle: 'initials',
    avatarAnimated: false,
    archiveFormat: 'tar.gz',
    rootAdmin: false,
    adminAccess: false,
    rolePermissions: [],
    useTotp: false,
    createdAt: new Date(0),
    updatedAt: new Date(0),
};

const deferred = () => {
    let resolve!: () => void;
    const promise = new Promise<void>((done) => {
        resolve = done;
    });
    return { promise, resolve };
};

let container: HTMLDivElement;
let root: Root;

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    await i18n.init({ lng: 'en', fallbackLng: 'en', resources: { en: {}, fr: {} } });
    vi.stubGlobal(
        'fetch',
        vi.fn(async (url: string) => ({
            json: async () =>
                url === '/locales/list.json'
                    ? { en: { name: 'English', flag: 'us' }, fr: { name: 'French', flag: 'fr' } }
                    : { cm: { display: 'CodeMirror' }, mo: { display: 'Monaco' } },
        })),
    );
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

const select = async (element: HTMLSelectElement, value: string) => {
    await act(async () => {
        element.value = value;
        element.dispatchEvent(new Event('change', { bubbles: true }));
    });
};

describe.each(['account', 'navbar'] as const)('%s language preference saves', (languageControl) => {
    it.each(['language first', 'editor first'] as const)(
        'merges overlapping saves into the latest user state when %s',
        async (responseOrder) => {
            const store = createStore({ user, settings: { data: { locale: 'en' } } });
            store.getActions().user.setUserData(initialUser);
            const language = deferred();
            const editor = deferred();
            vi.mocked(updateAccountLanguage).mockReturnValue(language.promise);
            vi.mocked(updateEditor).mockReturnValue(editor.promise);

            await act(async () => {
                root.render(
                    <StoreProvider store={store}>
                        <LocaleLoader />
                        {languageControl === 'account' ? <LanguageSwitcher /> : <NavbarLanguageSwitcher />}
                        <FileEditorSwitcher />
                    </StoreProvider>,
                );
            });

            if (languageControl === 'account') {
                await select(container.querySelectorAll('select')[0]!, 'fr');
            } else {
                await act(async () => {
                    container.querySelector('button')!.click();
                });
                await act(async () => {
                    Array.from(container.querySelectorAll('button'))
                        .find((button) => button.textContent === 'French')!
                        .click();
                });
            }
            await select(container.querySelectorAll('select')[languageControl === 'account' ? 1 : 0]!, 'mo');
            expect(updateAccountLanguage).toHaveBeenCalledWith({ language: 'fr' });
            expect(updateEditor).toHaveBeenCalledWith({ fileEditor: 'mo' });

            const recentChanges = { email: 'updated@example.com', avatarStyle: 'beam', archiveFormat: 'zip' as const };
            await act(async () => store.getActions().user.updateUserData(recentChanges));
            const responses = responseOrder === 'language first' ? [language, editor] : [editor, language];
            await act(async () => responses[0]!.resolve());
            expect.soft(store.getState().user.data).toMatchObject(recentChanges);
            await act(async () => responses[1]!.resolve());

            expect(store.getState().user.data).toEqual({
                ...initialUser,
                ...recentChanges,
                language: 'fr',
                fileEditor: 'mo',
            });
            expect(i18n.language).toBe('fr');
            expect(container.querySelectorAll('select')[languageControl === 'account' ? 1 : 0]!.value).toBe('mo');
            if (languageControl === 'account') {
                expect(container.querySelector('select')!.value).toBe('fr');
            }
        },
    );
});
