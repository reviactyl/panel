import { createElement } from 'react';
import { createRoot } from 'react-dom/client';
import type { Props } from '@/reviactyl/elements/CodemirrorEditor';

export async function mountHtmlEditor(container: HTMLElement, preference: string, html: string, save: () => void) {
    const [{ default: Editor }, { format }, parser] = await Promise.all([
        preference === 'cm'
            ? import('@/reviactyl/elements/CodemirrorEditor')
            : import('@/reviactyl/elements/MonacoEditor'),
        import('prettier/standalone'),
        import('prettier/plugins/html'),
    ]);
    let content = html;
    try {
        content = await format(html, {
            parser: 'html',
            plugins: [parser],
            tabWidth: 4,
            htmlWhitespaceSensitivity: 'strict',
        });
    } catch {
        // Keep incomplete markup editable.
    }
    let read: (() => Promise<string>) | undefined;
    const root = createRoot(container);
    const props: Props = {
        initialContent: content,
        mode: 'text/html',
        filename: 'email.html',
        style: { height: '100%', minHeight: 0 },
        onModeChanged: () => {},
        fetchContent: (callback) => {
            read = callback;
        },
        onContentSaved: save,
    };
    root.render(createElement(Editor, props));
    return {
        read: () => (read ? read() : Promise.reject(new Error('Editor is loading'))),
        destroy: () => root.unmount(),
    };
}
