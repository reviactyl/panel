import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { getByRole } from '@testing-library/dom';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import user from '@/state/user';
import LanguageSwitcher from './LanguageSwitcher';
import FileEditorSwitcher from './FileEditorSwitcher';

const mocks = vi.hoisted(() => ({ language: vi.fn(), editor: vi.fn(), changeLanguage: vi.fn() }));
vi.mock('@/api/account/updateAccountLanguage', () => ({ default: mocks.language }));
vi.mock('@/api/account/updateAccountEditor', () => ({ default: mocks.editor }));
vi.mock('@/i18n', () => ({ default: { on: vi.fn(), off: vi.fn(), changeLanguage: mocks.changeLanguage } }));
vi.mock('react-i18next', () => ({
    useTranslation: () => ({
        t: (key: string) =>
            (
                ({ 'overview.language': 'Panel Language', 'overview.editor': 'Preferred File Editor' }) as Record<
                    string,
                    string
                >
            )[key] || key,
    }),
}));

let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof createAccountStore>;
const createAccountStore = () => createStore({ user, settings: { data: { locale: 'en' } } });
const preferences = () => (
    <>
        <LanguageSwitcher />
        <FileEditorSwitcher />
    </>
);
const render = async (children = preferences()) => {
    await act(async () => root.render(<StoreProvider store={store}>{children}</StoreProvider>));
};

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.stubGlobal(
        'fetch',
        vi.fn(async (url: string) => ({
            json: async () =>
                url === '/locales/list.json'
                    ? { en: { name: 'English' }, de: { name: 'Deutsch' } }
                    : { cm: { display: 'CodeMirror' }, mo: { display: 'Monaco' } },
        })),
    );
    vi.clearAllMocks();
    mocks.language.mockResolvedValue(undefined);
    mocks.editor.mockResolvedValue(undefined);
    store = createAccountStore();
    store.getActions().user.updateUserData({ language: 'en', fileEditor: 'cm' });
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

it('names both selects through their visible labels and forwards label activation', async () => {
    await render();
    for (const name of ['Panel Language', 'Preferred File Editor']) {
        const select = getByRole(container, 'combobox', { name }) as HTMLSelectElement;
        const label = select.labels![0]!;
        expect(label.textContent).toBe(name);
        expect(label.control).toBe(select);
        const click = vi.fn();
        select.addEventListener('click', click);
        await act(async () => label.click());
        expect(click).toHaveBeenCalledOnce();
    }
});

it('keeps label associations stable and unique across repeated selectors', async () => {
    const children = () => (
        <>
            {preferences()}
            {preferences()}
        </>
    );
    await render(children());
    const selects = Array.from(container.querySelectorAll('select'));
    const ids = selects.map((select) => select.id);
    expect(ids.every(Boolean)).toBe(true);
    expect(new Set(ids).size).toBe(4);
    await render(children());
    expect(Array.from(container.querySelectorAll('select'), (select) => select.id)).toEqual(ids);
    selects.forEach((select) => expect(select.labels![0]!.control).toBe(select));
});

it('continues saving selected language and editor preferences', async () => {
    await render();
    const [language, editor] = Array.from(container.querySelectorAll('select'));
    await act(async () => {
        language!.value = 'de';
        language!.dispatchEvent(new Event('change', { bubbles: true }));
    });
    expect(mocks.language).toHaveBeenCalledExactlyOnceWith({ language: 'de' });
    expect(mocks.changeLanguage).toHaveBeenCalledExactlyOnceWith('de');
    expect(store.getState().user.data?.language).toBe('de');
    await act(async () => {
        editor!.value = 'mo';
        editor!.dispatchEvent(new Event('change', { bubbles: true }));
    });
    expect(mocks.editor).toHaveBeenCalledExactlyOnceWith({ fileEditor: 'mo' });
    expect(store.getState().user.data?.fileEditor).toBe('mo');
    expect(language!.value).toBe('de');
    expect(editor!.value).toBe('mo');
});

it('provides both editors without requesting the nonexistent editor-list endpoint', async () => {
    await render(<FileEditorSwitcher />);
    expect(Array.from(container.querySelectorAll('option'), (option) => option.value)).toEqual(['cm', 'mo']);
    expect(fetch).not.toHaveBeenCalled();
});
