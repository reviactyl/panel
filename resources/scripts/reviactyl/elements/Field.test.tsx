import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { Form, Formik } from 'formik';
import { getByRole } from '@testing-library/dom';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import Field from './Field';

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

it('names a field through its generated label and activates it when clicked', async () => {
    await act(async () => {
        root.render(
            <Formik initialValues={{ name: '' }} onSubmit={() => {}}>
                <Form>
                    <Field name='name' label='Schedule name' />
                </Form>
            </Formik>,
        );
    });

    const input = getByRole(container, 'textbox', { name: 'Schedule name' }) as HTMLInputElement;
    expect(input.id).not.toBe('');
    const label = input.labels![0]!;
    expect(label.htmlFor).toBe(input.id);
    const click = vi.fn();
    input.addEventListener('click', click);
    await act(async () => label.click());
    expect(click).toHaveBeenCalledOnce();
});

it('keeps generated ids stable and distinct across forms while preserving explicit ids', async () => {
    const render = (description: string) => (
        <>
            <Formik initialValues={{ name: '', minute: '' }} onSubmit={() => {}}>
                <Form>
                    <Field name='name' label='Schedule name' description={description} />
                    <Field id='schedule-minute' name='minute' label='Minute' />
                </Form>
            </Formik>
            <Formik initialValues={{ name: '' }} onSubmit={() => {}}>
                <Form>
                    <Field name='name' label='Backup name' />
                </Form>
            </Formik>
        </>
    );
    await act(async () => root.render(render('Initial description')));
    const schedule = getByRole(container, 'textbox', { name: 'Schedule name' }) as HTMLInputElement;
    const backup = getByRole(container, 'textbox', { name: 'Backup name' }) as HTMLInputElement;
    const minute = getByRole(container, 'textbox', { name: 'Minute' }) as HTMLInputElement;
    const ids = [schedule.id, backup.id, minute.id];
    expect(ids.every(Boolean)).toBe(true);
    expect(new Set(ids).size).toBe(3);
    expect(minute.id).toBe('schedule-minute');

    await act(async () => root.render(render('Updated description')));
    expect(Array.from(container.querySelectorAll('input'), (input) => input.id)).toEqual([ids[0], ids[2], ids[1]]);
    expect(backup.labels![0]!.control).toBe(backup);
    expect(minute.labels![0]!.control).toBe(minute);
});
