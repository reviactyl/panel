import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { getAllByRole, getByRole } from '@testing-library/dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { Theme, ThemeToggle } from './Theme';

vi.mock('react-i18next', () => ({
    useTranslation: () => ({
        t: (key: string) => {
            const translations: Record<string, string> = {
                'overview.theme-toggle': 'Theme Toggle',
                'overview.theme-light': 'Light',
                'overview.theme-dark': 'Dark',
                'overview.theme-system': 'System',
            };

            return translations[key] ?? key;
        },
    }),
}));

let container: HTMLDivElement;
let root: Root;

const render = () =>
    act(async () => {
        root.render(
            <Theme>
                <ThemeToggle />
            </Theme>,
        );
    });

const expectSelected = (name: string) => {
    expect(getByRole(container, 'button', { name, pressed: true })).toBeTruthy();
    expect(getAllByRole(container, 'button', { pressed: false })).toHaveLength(2);
};

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.stubGlobal(
        'matchMedia',
        vi.fn().mockReturnValue({ matches: true, addEventListener: vi.fn(), removeEventListener: vi.fn() }),
    );
    localStorage.clear();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    localStorage.clear();
    document.documentElement.classList.remove('light', 'dark');
    vi.unstubAllGlobals();
});

describe('theme choices', () => {
    it('names all three choices and updates the pressed state, applied theme, and storage', async () => {
        await render();
        expectSelected('System');
        expect(document.documentElement.classList.contains('dark')).toBe(true);

        for (const [name, value, applied] of [
            ['Light', 'light', 'light'],
            ['Dark', 'dark', 'dark'],
            ['System', 'system', 'dark'],
        ] as const) {
            const button = getByRole(container, 'button', { name }) as HTMLButtonElement;
            expect(button.type).toBe('button');
            await act(async () => button.click());
            expectSelected(name);
            expect(localStorage.getItem('theme')).toBe(value);
            expect(document.documentElement.classList.contains(applied)).toBe(true);
            expect(document.documentElement.classList.contains(applied === 'dark' ? 'light' : 'dark')).toBe(false);
        }
    });

    it.each(['light', 'dark', 'system'])('restores the stored %s choice when mounted again', async (value) => {
        localStorage.setItem('theme', value);
        await render();
        expectSelected(value[0]!.toUpperCase() + value.slice(1));
        const applied = value === 'system' ? 'dark' : value;
        expect(document.documentElement.classList.contains(applied)).toBe(true);
    });
});
