import { execFileSync } from 'node:child_process';
import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { getByRole, queryByRole } from '@testing-library/dom';
import { createInstance } from 'i18next';
import { I18nextProvider } from 'react-i18next';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { Dialog } from './';

const catalogs: Record<string, { close: string }> = JSON.parse(
    execFileSync(
        'php',
        [
            '-r',
            'foreach (glob("lang/*", GLOB_ONLYDIR) as $path) { $catalogs[basename($path)] = require "$path/strings.php"; } echo json_encode($catalogs, JSON_THROW_ON_ERROR);',
        ],
        { encoding: 'utf8' },
    ),
);

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

it.each(Object.entries(catalogs))(
    'names the shared close action in %s and preserves explicit dismissal',
    async (locale, strings) => {
        expect(strings.close?.trim()).toBeTruthy();
        const i18n = createInstance();
        await i18n.init({ lng: locale, fallbackLng: false, resources: { [locale]: { strings } } });
        const onClose = vi.fn();
        await act(async () =>
            root.render(
                <I18nextProvider i18n={i18n}>
                    <Dialog open title='Example dialog' onClose={onClose} preventExternalClose>
                        <input aria-label='Example field' />
                    </Dialog>
                </I18nextProvider>,
            ),
        );

        const dialog = getByRole(document.body, 'dialog', { name: 'Example dialog' });
        const close = getByRole(dialog, 'button', { name: strings.close });
        await act(async () => close.click());
        expect(onClose).toHaveBeenCalledOnce();
    },
);

it('continues to hide the close control when requested', async () => {
    const i18n = createInstance();
    await i18n.init({ lng: 'en', resources: { en: { strings: catalogs.en! } } });
    await act(async () =>
        root.render(
            <I18nextProvider i18n={i18n}>
                <Dialog open title='Locked dialog' onClose={vi.fn()} hideCloseIcon />
            </I18nextProvider>,
        ),
    );
    const dialog = getByRole(document.body, 'dialog', { name: 'Locked dialog' });
    expect(queryByRole(dialog, 'button')).toBeNull();
});
