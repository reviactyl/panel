import { describe, expect, it, vi } from 'vitest';
import { emailTemplateEditor, extractContent } from './emailTemplateEditor';

describe('email canvas content', () => {
    it('preserves placeholder tokens when saving resolved canvas text', () => {
        const content = document.createElement('div');
        content.innerHTML =
            '<p>Hello <span contenteditable="false" data-email-token="{{ recipient_name }}">User</span></p><a href="{{ action_url }}">Open</a>';
        expect(extractContent(content)).toBe('<p>Hello {{ recipient_name }}</p><a href="{{ action_url }}">Open</a>');
        expect(content.querySelector('span')?.textContent).toBe('User');
    });
    it('keeps formatting and custom tables while extracting just the editable content', () => {
        const content = document.createElement('div');
        content.innerHTML =
            '<h2 style="font-size:24px">Title</h2><table><tbody><tr><td>Details</td></tr></tbody></table>';
        expect(extractContent(content)).toBe(content.innerHTML);
    });
});

describe('failed HTML previews', () => {
    it.each(['reject', 'null', 'undefined'])('keeps HTML editable when a preview returns %s', async (result) => {
        const wire = {
            editorPreference: vi.fn(),
            loadTemplate: vi.fn(),
            saveTemplate: vi.fn(),
            previewTemplate:
                result === 'reject'
                    ? vi.fn().mockRejectedValue(new Error('Missing action link'))
                    : vi.fn().mockResolvedValue(result === 'null' ? null : undefined),
        };
        const editor = emailTemplateEditor(wire, { preview_error: 'Check the action link.' });
        const frame = document.createElement('iframe');
        Object.defineProperty(frame, 'contentDocument', { value: document.implementation.createHTMLDocument() });
        frame.contentDocument!.body.innerHTML = '<div data-email-content><p>Previous HTML</p></div>';
        Object.assign(editor.$refs, { editor: frame });
        editor.editorReady = true;
        editor.draft = { subject: 'Reset Password', html: '<p>HTML without its action link</p>' };
        editor.sourcePending = true;
        editor.saved = JSON.stringify({ subject: 'Reset Password', html: '<p>Previous HTML</p>' });
        expect(editor.focusSelection()).toBeNull();
        await editor.updatePreview();
        expect(editor.error).toBe('Check the action link.');
        expect(editor.sourcePending).toBe(false);
        expect(editor.content()!.innerHTML).toBe(editor.draft.html);
        editor.content()!.innerHTML = '<p>Further canvas edits</p>';
        editor.syncEditor();
        expect(editor.draft.html).toBe('<p>Further canvas edits</p>');
        expect(editor.dirty).toBe(true);
        frame.remove();
    });

    it('ignores an obsolete failed preview while newer HTML is pending', async () => {
        let rejectPreview!: (reason: Error) => void;
        const editor = emailTemplateEditor(
            {
                editorPreference: vi.fn(),
                loadTemplate: vi.fn(),
                saveTemplate: vi.fn(),
                previewTemplate: () =>
                    new Promise<string>((_, reject) => {
                        rejectPreview = reject;
                    }),
            },
            { preview_error: 'Preview failed' },
        );
        editor.sourcePending = true;
        const pending = editor.updatePreview();
        editor.revision++;
        rejectPreview(new Error('Old request failed'));
        await pending;
        expect(editor.sourcePending).toBe(true);
        expect(editor.error).toBe('');
    });
});

describe('rejected template saves', () => {
    it.each([null, undefined])('preserves the dirty draft when Livewire returns %s', async (result) => {
        const previewTemplate = vi.fn();
        const editor = emailTemplateEditor(
            {
                editorPreference: vi.fn(),
                loadTemplate: vi.fn(),
                previewTemplate,
                saveTemplate: vi.fn().mockResolvedValue(result),
            },
            { saved: 'Saved', save_error: 'Check the template' },
        );
        editor.busy = false;
        editor.dirty = true;
        editor.draft = { subject: 'Reset', html: '<p>Missing action link</p>' };
        editor.saved = 'previous template';
        await editor.save();
        expect(editor.draft.html).toBe('<p>Missing action link</p>');
        expect(editor.saved).toBe('previous template');
        expect(editor.dirty).toBe(true);
        expect(editor.status).toBe('');
        expect(editor.error).toBe('Check the template');
        expect(previewTemplate).not.toHaveBeenCalled();
    });
});
