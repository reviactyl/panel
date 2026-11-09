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
    let reject!: (error: Error) => void;
    const promise = new Promise<void>((done, fail) => {
        resolve = done;
        reject = fail;
    });
    return { promise, resolve, reject };
};

let container: HTMLDivElement;
let root: Root;

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.resetAllMocks();
    vi.spyOn(console, 'error').mockImplementation(() => undefined);
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
    vi.restoreAllMocks();
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

describe.each(['account', 'navbar', 'editor'] as const)('%s preference save failures', (control) => {
    it('restores the last saved value after a rejected request', async () => {
        const store = createStore({ user, settings: { data: { locale: 'en' } } });
        store.getActions().user.setUserData(initialUser);
        vi.mocked(updateAccountLanguage).mockRejectedValue(new Error('offline'));
        vi.mocked(updateEditor).mockRejectedValue(new Error('offline'));

        await act(async () => {
            root.render(
                <StoreProvider store={store}>
                    <LocaleLoader />
                    {control === 'account' ? (
                        <LanguageSwitcher />
                    ) : control === 'navbar' ? (
                        <NavbarLanguageSwitcher />
                    ) : null}
                    {control === 'editor' ? <FileEditorSwitcher /> : null}
                </StoreProvider>,
            );
        });

        if (control === 'account') {
            await select(container.querySelector('select')!, 'fr');
            expect(container.querySelector('select')!.value).toBe('en');
            expect(i18n.language).toBe('en');
            expect(store.getState().user.data?.language).toBe('en');
        } else if (control === 'navbar') {
            await act(async () => container.querySelector('button')!.click());
            await act(async () => {
                Array.from(container.querySelectorAll('button'))
                    .find((button) => button.textContent === 'French')!
                    .click();
            });
            expect(container.querySelector('button')!.getAttribute('aria-label')).toBe('overview.language: English');
            expect(i18n.language).toBe('en');
            expect(store.getState().user.data?.language).toBe('en');
        } else {
            await select(container.querySelector('select')!, 'mo');
            expect(container.querySelector('select')!.value).toBe('cm');
            expect(store.getState().user.data?.fileEditor).toBe('cm');
        }
    });
});

describe.each(['account', 'navbar', 'editor'] as const)('%s delayed saves', (control) => {
    it.each(['first fails', 'second fails', 'both succeed'] as const)(
        'serializes repeated saves and retains the confirmed choice when %s',
        async (outcome) => {
            const store = createStore({ user, settings: { data: { locale: 'en' } } });
            store.getActions().user.setUserData(initialUser);
            const first = deferred();
            const second = deferred();
            const api = control === 'editor' ? vi.mocked(updateEditor) : vi.mocked(updateAccountLanguage);
            api.mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise);
            await act(async () => {
                root.render(
                    <StoreProvider store={store}>
                        <LocaleLoader />
                        {control === 'editor' ? (
                            <FileEditorSwitcher />
                        ) : control === 'navbar' ? (
                            <NavbarLanguageSwitcher />
                        ) : (
                            <LanguageSwitcher />
                        )}
                    </StoreProvider>,
                );
            });
            const choose = async (value: string) => {
                if (control !== 'navbar') return select(container.querySelector('select')!, value);
                await act(async () => container.querySelector('button')!.click());
                await act(async () => {
                    Array.from(container.querySelectorAll('button'))
                        .find((button) => button.textContent === (value === 'fr' ? 'French' : 'English'))!
                        .click();
                });
            };
            const displayed = () =>
                control === 'navbar'
                    ? container.querySelector('button')!.getAttribute('aria-label')
                    : container.querySelector('select')!.value;
            const original = control === 'editor' ? 'cm' : 'en';
            const changed = control === 'editor' ? 'mo' : 'fr';
            await choose(changed);
            await choose(original);
            expect(api).toHaveBeenCalledTimes(1);
            expect(displayed()).toBe(control === 'navbar' ? 'overview.language: English' : original);
            await act(async () => {
                store.getActions().user.updateUserData({ email: 'new@example.com' });
                if (outcome === 'first fails') first.reject(new Error('first rejected'));
                else first.resolve();
            });
            expect(api).toHaveBeenCalledTimes(2);
            await act(async () => {
                if (outcome === 'second fails') second.reject(new Error('second rejected'));
                else second.resolve();
            });
            const confirmed = outcome === 'second fails' ? changed : original;
            expect(store.getState().user.data).toMatchObject({
                [control === 'editor' ? 'fileEditor' : 'language']: confirmed,
                email: 'new@example.com',
            });
            expect(displayed()).toBe(
                control === 'navbar' ? `overview.language: ${confirmed === 'fr' ? 'French' : 'English'}` : confirmed,
            );
            if (control !== 'editor') expect(i18n.language).toBe(confirmed);
        },
    );
});

it('serializes language saves across selectors and remounts without blocking editor saves', async () => {
    const store = createStore({ user, settings: { data: { locale: 'en' } } });
    store.getActions().user.setUserData(initialUser);
    const first = deferred();
    const second = deferred();
    vi.mocked(updateAccountLanguage).mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise);
    vi.mocked(updateEditor).mockResolvedValue(undefined);
    const render = async (navbar: boolean) =>
        act(async () =>
            root.render(
                <StoreProvider store={store}>
                    <LocaleLoader />
                    {navbar ? <NavbarLanguageSwitcher /> : <LanguageSwitcher />}
                    <FileEditorSwitcher />
                </StoreProvider>,
            ),
        );
    await render(false);
    await select(container.querySelector('select')!, 'fr');
    await render(true);
    await act(async () => container.querySelector('button')!.click());
    await act(async () =>
        Array.from(container.querySelectorAll('button'))
            .find((b) => b.textContent === 'English')!
            .click(),
    );
    await select(container.querySelector('select')!, 'mo');
    expect(updateAccountLanguage).toHaveBeenCalledTimes(1);
    expect(store.getState().user.data?.fileEditor).toBe('mo');
    await act(async () => first.resolve());
    expect(updateAccountLanguage).toHaveBeenCalledTimes(2);
    await act(async () => second.reject(new Error('rejected')));
    expect(store.getState().user.data).toMatchObject({ language: 'fr', fileEditor: 'mo' });
    expect(i18n.language).toBe('fr');
    expect(container.querySelector('button')!.getAttribute('aria-label')).toBe('overview.language: French');
});

it('ignores completion and queued requests after the account changes', async () => {
    const store = createStore({ user, settings: { data: { locale: 'en' } } });
    store.getActions().user.setUserData(initialUser);
    const first = deferred();
    vi.mocked(updateAccountLanguage).mockReturnValue(first.promise);
    await act(async () =>
        root.render(
            <StoreProvider store={store}>
                <LocaleLoader />
                <LanguageSwitcher />
            </StoreProvider>,
        ),
    );
    await select(container.querySelector('select')!, 'fr');
    await select(container.querySelector('select')!, 'en');
    await act(async () => store.getActions().user.setUserData({ ...initialUser, uuid: 'different-user' }));
    await act(async () => first.resolve());
    expect(updateAccountLanguage).toHaveBeenCalledTimes(1);
    expect(store.getState().user.data).toEqual({ ...initialUser, uuid: 'different-user' });
    expect(i18n.language).toBe('en');
    expect(container.querySelector('select')!.value).toBe('en');
});

it('continues changing a guest language locally without an account request', async () => {
    const store = createStore({ user, settings: { data: { locale: 'en' } } });
    await act(async () =>
        root.render(
            <StoreProvider store={store}>
                <NavbarLanguageSwitcher />
            </StoreProvider>,
        ),
    );
    await act(async () => container.querySelector('button')!.click());
    await act(async () =>
        Array.from(container.querySelectorAll('button'))
            .find((b) => b.textContent === 'French')!
            .click(),
    );
    expect(i18n.language).toBe('fr');
    expect(updateAccountLanguage).not.toHaveBeenCalled();
    expect(container.querySelector('button')!.getAttribute('aria-label')).toBe('overview.language: French');
});
