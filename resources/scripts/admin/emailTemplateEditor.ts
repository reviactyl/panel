import type { mountHtmlEditor } from './emailHtmlEditor';
type Template = { subject: string; html: string };
type Wire = {
    editorPreference: () => Promise<string>;
    loadTemplate: (type: string) => Promise<Template | null | undefined>;
    previewTemplate: (type: string, template: Template) => Promise<string | null | undefined>;
    saveTemplate: (type: string, template: Template) => Promise<Template | null | undefined>;
};

function isTemplate(value: unknown): value is Template {
    return (
        typeof value === 'object' &&
        value !== null &&
        'subject' in value &&
        typeof value.subject === 'string' &&
        'html' in value &&
        typeof value.html === 'string'
    );
}

export function extractContent(content: HTMLElement): string {
    const clone = content.cloneNode(true) as HTMLElement;
    clone.querySelectorAll('[data-email-token]').forEach((token) => {
        token.replaceWith(clone.ownerDocument.createTextNode(token.getAttribute('data-email-token') || ''));
    });
    return clone.innerHTML.replace(/\u200b/g, '');
}

export function emailTemplateEditor(
    wire: Wire,
    labels: Record<string, string>,
    values: Record<string, Record<string, string>> = {},
) {
    return {
        labels,
        values,
        type: 'AccountCreated',
        selectedType: 'AccountCreated',
        viewport: sessionStorage.getItem('designify-preview-viewport') || 'desktop',
        draft: { subject: '', html: '' } as Template,
        saved: '',
        dirty: false,
        busy: true,
        error: '',
        status: '',
        resourceUrl: '',
        urlCommand: 'createLink',
        htmlEditor: null as Awaited<ReturnType<typeof mountHtmlEditor>> | null,
        htmlLoading: false,
        htmlSession: 0,
        fontSize: '16',
        textColor: '#d4d4d8',
        typingStyles: {} as Record<string, string>,
        revision: 0,
        sourcePending: false,
        editorReady: false,
        range: null as Range | null,
        sizeTimer: undefined as ReturnType<typeof setTimeout> | undefined,
        timer: undefined as ReturnType<typeof setTimeout> | undefined,
        navigate: null as ((event: Event) => void) | null,
        beforeUnload: null as ((event: BeforeUnloadEvent) => void) | null,
        $refs: {} as {
            editor: HTMLIFrameElement;
            htmlDialog: HTMLDialogElement;
            urlDialog: HTMLDialogElement;
            htmlEditor: HTMLDivElement;
        },
        get placeholders() {
            return Object.keys(this.values[this.type] || {}).filter((token) => token !== 'action_text');
        },
        setViewport(viewport: string) {
            this.viewport = viewport;
            sessionStorage.setItem('designify-preview-viewport', viewport);
        },
        publishState() {
            window.dispatchEvent(new CustomEvent('email-state', { detail: { dirty: this.dirty, busy: this.busy } }));
        },
        async init() {
            this.beforeUnload = (event: BeforeUnloadEvent) => {
                if (
                    !this.dirty ||
                    (window as Window & { __designifySkipBeforeUnload?: boolean }).__designifySkipBeforeUnload
                )
                    return;
                event.preventDefault();
                event.returnValue = '';
            };
            window.addEventListener('beforeunload', this.beforeUnload);
            this.navigate = (event: Event) => {
                if (!this.dirty) return;
                const url = (event as CustomEvent<{ url: URL }>).detail?.url;
                if (!url) return;
                event.preventDefault();
                window.location.assign(url.toString());
            };
            document.addEventListener('livewire:navigate', this.navigate);
            await this.load();
        },
        destroy() {
            this.stopFontSize();
            this.closeHtml();
            clearTimeout(this.timer);
            if (this.navigate) document.removeEventListener('livewire:navigate', this.navigate);
            if (this.beforeUnload) window.removeEventListener('beforeunload', this.beforeUnload);
        },
        async load() {
            this.busy = true;
            this.publishState();
            try {
                const draft = await wire.loadTemplate(this.type);
                if (!isTemplate(draft)) throw new Error('Template unavailable');
                this.draft = draft;
                this.selectedType = this.type;
                this.saved = JSON.stringify(this.draft);
                this.dirty = false;
                await this.updatePreview();
            } catch {
                this.type = this.selectedType;
                this.error = labels.load_error!;
            } finally {
                this.busy = false;
                this.publishState();
            }
        },
        async selectTemplate() {
            this.syncEditor();
            if (this.dirty && !window.confirm(labels.discard!)) {
                this.type = this.selectedType;
                return;
            }
            this.revision++;
            clearTimeout(this.timer);
            await this.load();
        },
        changed(render = false) {
            if (render) this.sourcePending = true;
            this.revision++;
            this.dirty = JSON.stringify(this.draft) !== this.saved;
            this.status = '';
            this.publishState();
            clearTimeout(this.timer);
            if (render) this.timer = setTimeout(() => void this.updatePreview(), 400);
        },
        async updatePreview() {
            const revision = this.revision;
            try {
                const html = await wire.previewTemplate(this.type, { ...this.draft });
                if (typeof html !== 'string') throw new Error('Preview unavailable');
                if (revision !== this.revision) return;
                const document = new DOMParser().parseFromString(html, 'text/html');
                const content = document.querySelector('[data-email-content]');
                if (content) content.innerHTML = this.draft.html;
                const csp = document.createElement('meta');
                csp.httpEquiv = 'Content-Security-Policy';
                csp.content =
                    "default-src 'none'; img-src https: http:; style-src 'unsafe-inline'; base-uri 'none'; form-action 'none'";
                document.head.prepend(csp);
                this.range = null;
                this.editorReady = false;
                this.$refs.editor.srcdoc = '<!doctype html>' + document.documentElement.outerHTML;
                this.error = '';
            } catch {
                if (revision !== this.revision) return;
                const content = this.content();
                if (content && this.editorReady && this.sourcePending) {
                    this.range = null;
                    content.innerHTML = this.draft.html;
                    this.sourcePending = false;
                }
                this.error = labels.preview_error!;
            }
        },
        content() {
            return this.$refs.editor.contentDocument?.querySelector<HTMLElement>('[data-email-content]') || null;
        },
        syncEditor() {
            if (this.sourcePending || !this.editorReady) return;
            const content = this.content();
            if (!content) return;
            const html = extractContent(content);
            if (html !== this.draft.html) {
                this.draft.html = html;
                this.changed();
            }
        },
        bindEditor() {
            const document = this.$refs.editor.contentDocument;
            const content = this.content();
            if (!document || !content) return;
            // Only the email body is editable; the theme remains the delivery wrapper.
            content.innerHTML = this.draft.html;
            this.typingStyles = {};
            this.sourcePending = false;
            this.editorReady = true;
            content.contentEditable = 'true';
            content.setAttribute('role', 'textbox');
            content.setAttribute('aria-label', labels.editor!);
            content.style.outline = 'none';
            document.addEventListener('selectionchange', () => {
                const selection = document.getSelection();
                if (selection?.rangeCount && content.contains(selection.anchorNode)) {
                    this.range = selection.getRangeAt(0).cloneRange();
                    const element =
                        selection.anchorNode?.nodeType === 1
                            ? (selection.anchorNode as Element)
                            : selection.anchorNode?.parentElement;
                    const anchor = element?.closest('a');
                    if (anchor) this.resourceUrl = anchor.getAttribute('href') || '';
                }
            });
            const resetTyping = () => {
                this.typingStyles = {};
            };
            content.addEventListener('pointerdown', resetTyping);
            content.addEventListener('keydown', (event) => {
                if (
                    ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown'].includes(
                        event.key,
                    )
                )
                    resetTyping();
            });
            content.addEventListener('beforeinput', (event) => {
                if (event.inputType !== 'insertText' || event.isComposing || !event.data) return;
                const selection = document.getSelection();
                if (!selection?.rangeCount) return;
                const range = selection.getRangeAt(0);
                if (range.collapsed && !Object.keys(this.typingStyles).length) return;
                let node = range.startContainer;
                if (node.nodeType === Node.ELEMENT_NODE) node = node.childNodes[range.startOffset] || node;
                const element = node.nodeType === Node.ELEMENT_NODE ? (node as Element) : node.parentElement;
                if (!element) return;
                const computed = document.defaultView!.getComputedStyle(element);
                const span = document.createElement('span');
                for (const property of [
                    'color',
                    'font-size',
                    'font-family',
                    'font-weight',
                    'font-style',
                    'text-decoration-line',
                ]) {
                    span.style.setProperty(
                        property,
                        this.typingStyles[property] || computed.getPropertyValue(property),
                    );
                }
                if (
                    range.collapsed &&
                    Object.entries(this.typingStyles).every(([property, value]) => {
                        const probe = document.createElement('span');
                        probe.style.setProperty(property, value);
                        content.append(probe);
                        const normalized = document.defaultView!.getComputedStyle(probe).getPropertyValue(property);
                        probe.remove();
                        return computed.getPropertyValue(property) === normalized;
                    })
                )
                    return;
                event.preventDefault();
                range.deleteContents();
                span.textContent = '\u200b';
                range.insertNode(span);
                range.setStart(span.firstChild!, 1);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                document.execCommand('insertText', false, event.data);
                this.syncEditor();
            });
            content.addEventListener('input', () => this.syncEditor());
            document.addEventListener('click', (event) => {
                if ((event.target as Element).closest('a')) event.preventDefault();
            });
        },
        focusSelection() {
            if (this.sourcePending || !this.editorReady) return null;
            const document = this.$refs.editor.contentDocument;
            const content = this.content();
            if (!document || !content) return null;
            const savedRange = this.range?.cloneRange();
            content.focus();
            const selection = document.getSelection();
            if (savedRange && content.contains(savedRange.commonAncestorContainer)) {
                selection?.removeAllRanges();
                selection?.addRange(savedRange);
            }
            return document;
        },
        format(command: string, value?: string) {
            const document = this.focusSelection();
            document?.execCommand('styleWithCSS', false, 'false');
            document?.execCommand(command, false, value);
            const toggle = {
                bold: ['font-weight', 'bold', 'normal'],
                italic: ['font-style', 'italic', 'normal'],
                underline: ['text-decoration-line', 'underline', 'none'],
            }[command];
            if (document && toggle)
                this.typingStyles[toggle[0]!] = document.queryCommandState(command) ? toggle[1]! : toggle[2]!;

            this.syncEditor();
        },
        styleSelection(property: string, value: string) {
            this.typingStyles[property] = value;
            const document = this.focusSelection();
            const selection = document?.getSelection();
            if (!document || !selection?.rangeCount) return;
            const range = selection.getRangeAt(0);
            const span = document.createElement('span');
            span.style.setProperty(property, value);
            const collapsed = range.collapsed;
            span.append(collapsed ? document.createTextNode('\u200b') : range.extractContents());
            range.insertNode(span);
            selection.selectAllChildren(span);
            if (collapsed) selection.collapseToEnd();
            this.range = selection.getRangeAt(0).cloneRange();
            this.syncEditor();
        },
        applyFontSize(step = 0) {
            const size = Number(this.fontSize) + step;
            if (!Number.isFinite(size) || size <= 0 || size > 1000) return;
            this.fontSize = String(size);
            this.styleSelection('font-size', size + 'px');
        },
        startFontSize(step: number, event: PointerEvent) {
            if (event.button !== 0) return;
            this.stopFontSize();
            (event.currentTarget as HTMLElement).setPointerCapture(event.pointerId);
            this.applyFontSize(step);
            let delay = 350;
            const repeat = () => {
                this.applyFontSize(step);
                delay = Math.max(55, delay * 0.8);
                this.sizeTimer = setTimeout(repeat, delay);
            };
            this.sizeTimer = setTimeout(repeat, delay);
        },
        stopFontSize() {
            clearTimeout(this.sizeTimer);
        },
        insertPlaceholder(token: string) {
            const document = this.focusSelection();
            if (!document) return;
            document.execCommand('insertText', false, '{{ ' + token + ' }}');
            this.syncEditor();
        },
        async closeDialog(dialog: HTMLDialogElement) {
            if (!dialog.open || dialog.dataset.closing) return;
            dialog.dataset.closing = 'true';
            await Promise.allSettled(dialog.getAnimations().map((animation) => animation.finished));
            dialog.close();
            delete dialog.dataset.closing;
        },
        async openHtml() {
            this.syncEditor();
            this.$refs.htmlDialog.showModal();
            const session = ++this.htmlSession;
            this.htmlLoading = true;
            try {
                const { mountHtmlEditor } = await import('./emailHtmlEditor');
                const editor = await mountHtmlEditor(
                    this.$refs.htmlEditor,
                    await wire.editorPreference(),
                    this.draft.html,
                    () => void this.applyHtml(),
                );
                if (session !== this.htmlSession) editor.destroy();
                else this.htmlEditor = editor;
            } catch {
                this.error = labels.load_error!;
                await this.closeDialog(this.$refs.htmlDialog);
            } finally {
                this.htmlLoading = false;
            }
        },
        closeHtml() {
            this.htmlSession++;
            this.htmlEditor?.destroy();
            this.htmlEditor = null;
        },
        async applyHtml() {
            if (!this.htmlEditor || this.htmlLoading) return;
            try {
                this.draft.html = await this.htmlEditor.read();
                this.changed(true);
                await this.closeDialog(this.$refs.htmlDialog);
            } catch {
                this.error = labels.load_error!;
            }
        },
        openUrl(command: string) {
            this.urlCommand = command;
            this.resourceUrl = '';
            const selection = this.$refs.editor.contentDocument?.getSelection();
            const node = selection?.anchorNode;
            const element = node?.nodeType === 1 ? (node as Element) : node?.parentElement;
            if (command === 'createLink') this.resourceUrl = element?.closest('a')?.getAttribute('href') || '';
            this.$refs.urlDialog.showModal();
        },
        async insertUrl() {
            try {
                const url = new URL(this.resourceUrl.trim());
                if (!['https:', 'http:'].includes(url.protocol)) return;
                await this.closeDialog(this.$refs.urlDialog);
                this.format(this.urlCommand, url.href);
            } catch {
                return;
            }
        },
        async save() {
            if (this.busy) return;
            clearTimeout(this.timer);
            this.syncEditor();
            this.revision++;
            this.busy = true;
            this.publishState();
            try {
                const draft = await wire.saveTemplate(this.type, { ...this.draft });
                if (!isTemplate(draft)) throw new Error('Template rejected');
                this.draft = draft;
                this.saved = JSON.stringify(this.draft);
                this.dirty = false;
                this.status = labels.saved!;
                await this.updatePreview();
            } catch {
                this.error = labels.save_error!;
            } finally {
                this.busy = false;
                this.publishState();
            }
        },
    };
}
export default emailTemplateEditor;
