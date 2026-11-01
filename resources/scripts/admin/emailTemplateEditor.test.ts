import { describe, expect, it } from 'vitest';
import { extractContent } from './emailTemplateEditor';

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
