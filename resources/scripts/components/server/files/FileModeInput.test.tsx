import { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { Form, Formik } from 'formik';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import FileModeInput, { isValidFileMode } from './FileModeInput';

vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));

let container: HTMLDivElement;
let root: Root;
const submit = vi.fn();

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    submit.mockReset();
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

const render = async (mode = '0644') => {
    await act(async () => {
        root.render(
            <Formik
                initialValues={{ mode }}
                onSubmit={submit}
                validate={({ mode }) => (isValidFileMode(mode) ? {} : { mode: 'Invalid' })}
            >
                <Form>
                    <FileModeInput />
                    <button type='submit'>Update</button>
                </Form>
            </Formik>,
        );
    });
};
const input = () => container.querySelector<HTMLInputElement>('#file_mode')!;
const boxes = () => Array.from(container.querySelectorAll<HTMLInputElement>('input[type="checkbox"]'));
const type = async (value: string) => {
    await act(async () => {
        input().focus();
        Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value')!.set!.call(input(), value);
        input().dispatchEvent(new Event('input', { bubbles: true }));
    });
};

describe('file permission editing', () => {
    it('synchronizes typed octal modes and checkbox edits, preserving the leading digit', async () => {
        await render();
        expect(boxes().map((box) => box.checked)).toEqual([true, true, false, true, false, false, true, false, false]);
        await type('4755');
        expect(boxes().map((box) => box.checked)).toEqual([true, true, true, true, false, true, true, false, true]);
        await act(async () => boxes()[4]!.click());
        expect(input().value).toBe('4775');
        await type('000');
        expect(boxes().every((box) => !box.checked)).toBe(true);
    });

    it('keeps the grid stable during incomplete input and waits until blur to show an error', async () => {
        await render('755');
        await type('7');
        expect(input().getAttribute('aria-invalid')).toBeNull();
        expect(boxes()[8]!.checked).toBe(true);
        await act(async () => input().blur());
        expect(input().getAttribute('aria-invalid')).toBe('true');
        await type('888');
        expect(input().getAttribute('aria-invalid')).toBeNull();
        await act(async () =>
            container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
        );
        expect(submit).not.toHaveBeenCalled();
        expect(input().getAttribute('aria-invalid')).toBe('true');
        await type('88');
        expect(input().getAttribute('aria-invalid')).toBeNull();
        await act(async () => boxes()[8]!.click());
        expect(input().value).toBe('754');
        expect(input().getAttribute('aria-invalid')).toBeNull();
    });

    it('allows starting a blank bulk selection using the grid', async () => {
        await render('');
        await act(async () => boxes()[0]!.click());
        expect(input().value).toBe('400');
    });

    it.each(['', '7', '75', '888', '9999', '10000', '-755', ' 755', '755 ', '7.55', 'rwx'])(
        'rejects invalid mode %j',
        (mode) => {
            expect(isValidFileMode(mode)).toBe(false);
        },
    );
});
