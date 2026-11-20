import { renderToStaticMarkup } from 'react-dom/server';
import { describe, expect, it } from 'vitest';
import AlertMarkdown from './AlertMarkdown';
import { isPlainAlertMessage } from './Announcement';

function render(message: string) {
    const container = document.createElement('div');
    container.innerHTML = renderToStaticMarkup(<AlertMarkdown message={message} />);
    return container;
}

describe('alert Markdown', () => {
    it('renders bold text and inline code inside a link', () => {
        const container = render(
            '**Welcome to Reviactyl!** Feel free to look around, and check out [`Designer ↗`](https://example.com) in admin settings!',
        );
        expect(container.querySelector('strong')?.textContent).toBe('Welcome to Reviactyl!');
        const link = container.querySelector('a');
        expect(link?.getAttribute('href')).toBe('https://example.com');
        expect(link?.querySelector('code')?.textContent).toBe('Designer ↗');
        expect(link?.getAttribute('rel')).toBe('noopener noreferrer');
    });

    it('renders paragraphs, emphasis, lists and fenced code', () => {
        const container = render('First *paragraph*.\n\n- One\n- Two\n\n```\n**literal**\n```');
        expect(container.querySelector('em')?.textContent).toBe('paragraph');
        expect(container.querySelectorAll('li')).toHaveLength(2);
        expect(container.querySelector('pre code')?.textContent).toBe('**literal**\n');
    });

    it('blocks raw HTML, executable links and remote images', () => {
        const container = render(
            '<script>alert(1)</script>\n\n<img src=x onerror=alert(1)>\n\n[unsafe](javascript:alert%281%29) ![tracking](https://example.com/pixel)',
        );
        expect(container.querySelector('script, img')).toBeNull();
        expect(container.querySelector('a')?.getAttribute('href')).toBe('');
    });

    it.each([
        'Signing in with Discord is temporarily unavailable. Use your password instead.',
        'Maintenance tonight at 02:00 UTC (about 30 minutes); servers may restart.',
        'Visit https://status.example.com for updates, or email ops@example.com.',
        'Ünïcødé — “quotes” and emoji 🚀 stay as they are.',
    ])('renders plain message %j exactly like the Markdown renderer', (message) => {
        expect(isPlainAlertMessage(message)).toBe(true);
        expect(renderToStaticMarkup(<AlertMarkdown message={message} />)).toContain(
            renderToStaticMarkup(<p>{message}</p>),
        );
        expect(render(message).querySelector('div')?.children).toHaveLength(1);
    });

    it.each([
        '**bold**',
        'snake_case',
        'a `code` span',
        '# heading',
        '[link](https://example.com)',
        '<b>html</b>',
        'Tom &amp; Jerry',
        'line\nbreak',
        '- list item',
        '1. step',
        '2) step',
        '    indented code',
        ' leading space',
        'Title\n===',
        '> quote',
        'escaped \\* star',
        'hard break  ',
        '',
    ])('leaves %j to the Markdown renderer', (message) => {
        expect(isPlainAlertMessage(message)).toBe(false);
    });
});
