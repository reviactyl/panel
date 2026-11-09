import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { Field, Form, Formik } from 'formik';
import { getByRole } from '@testing-library/dom';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import Input, { Textarea } from './Input';
import FormikFieldWrapper from './FormikFieldWrapper';

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

it('names account inputs and textareas and preserves their Formik validation and submission', async () => {
    const submit = vi.fn();
    const validate = vi.fn((value: string) => (value ? undefined : 'Required'));
    await act(async () => {
        root.render(
            <Formik initialValues={{ description: '', allowedIps: '', name: '', publicKey: '' }} onSubmit={submit}>
                <Form>
                    <FormikFieldWrapper name='description' label='Description' validate={validate}>
                        <Field name='description' as={Input} />
                    </FormikFieldWrapper>
                    <FormikFieldWrapper name='allowedIps' label='Allowed IPs'>
                        <Field name='allowedIps' as={Textarea} />
                    </FormikFieldWrapper>
                    <FormikFieldWrapper name='name' label='Key Name'>
                        <Field name='name' as={Input} />
                    </FormikFieldWrapper>
                    <FormikFieldWrapper name='publicKey' label='Public Key'>
                        <Field name='publicKey' as={Textarea} />
                    </FormikFieldWrapper>
                </Form>
            </Formik>,
        );
    });
    const inputs = ['Description', 'Allowed IPs', 'Key Name', 'Public Key'].map(
        (name) => getByRole(container, 'textbox', { name }) as HTMLInputElement | HTMLTextAreaElement,
    );
    expect(new Set(inputs.map((input) => input.id)).size).toBe(4);
    for (const input of inputs) {
        expect(input.id).not.toBe('');
        expect(input.labels![0]!.control).toBe(input);
        const click = vi.fn();
        input.addEventListener('click', click);
        await act(async () => input.labels![0]!.click());
        expect(click).toHaveBeenCalledOnce();
    }
    const form = container.querySelector('form')!;
    await act(async () => form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })));
    expect(submit).not.toHaveBeenCalled();
    expect(container.textContent).toContain('Required');
    for (const input of inputs) {
        await act(async () => {
            const prototype = input.tagName === 'TEXTAREA' ? HTMLTextAreaElement.prototype : HTMLInputElement.prototype;
            Object.getOwnPropertyDescriptor(prototype, 'value')!.set!.call(input, `${input.name} value`);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
    await act(async () => form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })));
    expect(submit).toHaveBeenCalledWith(
        {
            description: 'description value',
            allowedIps: 'allowedIps value',
            name: 'name value',
            publicKey: 'publicKey value',
        },
        expect.any(Object),
    );
});

it('keeps generated ids stable and unique across forms and preserves explicit child and wrapper ids', async () => {
    const render = (description: string) => (
        <>
            <Formik initialValues={{ name: '', description: '', publicKey: '' }} onSubmit={() => {}}>
                <Form>
                    <FormikFieldWrapper name='name' label='First name' description={description}>
                        <Field name='name' as={Input} />
                    </FormikFieldWrapper>
                    <FormikFieldWrapper id='wrapper-id' name='description' label='Description'>
                        <Field id='overridden-id' name='description' as={Input} />
                    </FormikFieldWrapper>
                    <FormikFieldWrapper name='publicKey' label='Public Key'>
                        <Field id='child-id' name='publicKey' as={Textarea} />
                    </FormikFieldWrapper>
                </Form>
            </Formik>
            <Formik initialValues={{ name: '' }} onSubmit={() => {}}>
                <Form>
                    <FormikFieldWrapper name='name' label='Second name'>
                        {({ id }) => (
                            <div>
                                <Field name='name' as={Input} id={id} />
                            </div>
                        )}
                    </FormikFieldWrapper>
                </Form>
            </Formik>
        </>
    );
    await act(async () => root.render(render('Before')));
    const controls = ['First name', 'Description', 'Public Key', 'Second name'].map(
        (name) => getByRole(container, 'textbox', { name }) as HTMLInputElement,
    );
    const ids = controls.map((input) => input.id);
    expect(new Set(ids).size).toBe(4);
    expect(ids.slice(1, 3)).toEqual(['wrapper-id', 'child-id']);
    await act(async () => root.render(render('After')));
    expect(controls.map((input) => input.id)).toEqual(ids);
    for (const input of controls) expect(input.labels![0]!.control).toBe(input);
});
