import { Terminal, ITerminalAddon } from '@xterm/xterm';
import { SearchAddon } from '@xterm/addon-search';

export class SearchBarAddon implements ITerminalAddon {
    private terminal!: Terminal;
    private element?: HTMLDivElement;
    private input?: HTMLInputElement;
    private searchKey = '';

    constructor(private searchAddon: SearchAddon) {}

    activate(terminal: Terminal): void {
        this.terminal = terminal;
    }

    dispose(): void {
        this.hide();
    }

    show(): void {
        if (!this.terminal?.element) return;

        if (this.element) {
            this.element.style.visibility = 'visible';
            this.focus();
            return;
        }

        const parent = this.terminal.element.parentElement as HTMLElement;
        if (!parent) return;

        if (!['relative', 'absolute', 'fixed'].includes(parent.style.position)) {
            parent.style.position = 'relative';
        }

        this.element = document.createElement('div');
        this.element.style.position = 'absolute';
        this.element.style.top = '8px';
        this.element.style.right = '8px';
        this.element.style.display = 'flex';
        this.element.style.alignItems = 'center';
        this.element.style.gap = '6px';
        this.element.style.padding = '6px';
        this.element.style.background = 'rgb(var(--color-700))';
        this.element.style.border = '1px solid rgb(var(--color-600))';
        this.element.style.borderRadius = '10px';
        this.element.style.boxShadow = '0 2px 6px rgb(var(--color-primary) / 0.5)';
        this.element.style.zIndex = '1000';

        this.input = document.createElement('input');
        this.input.type = 'text';
        this.input.placeholder = 'Search...';
        this.input.style.padding = '4px 6px';
        this.input.style.fontSize = '12px';
        this.input.style.background = 'transparent';
        this.input.style.color = 'rgb(var(--color-200))';
        this.input.style.border = '1px solid rgb(var(--color-600))';
        this.input.style.borderRadius = '6px';
        this.input.style.outline = 'none';

        const prevBtn = this.createButton(`
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M11.47 7.72a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06L12 9.31l-6.97 6.97a.75.75 0 0 1-1.06-1.06l7.5-7.5Z" clip-rule="evenodd" />
            </svg>
        `);

        const nextBtn = this.createButton(`
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" />
            </svg>
        `);

        const closeBtn = this.createButton(`
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        `);

        prevBtn.addEventListener('click', () => {
            this.searchAddon.findPrevious(this.searchKey, { incremental: false });
        });

        nextBtn.addEventListener('click', () => {
            this.searchAddon.findNext(this.searchKey, { incremental: false });
        });

        closeBtn.addEventListener('click', () => this.hide());

        this.input.addEventListener('keyup', (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                this.hide();
                this.terminal.focus();
                return;
            }

            this.searchKey = this.input!.value;

            this.searchAddon.findNext(this.searchKey, {
                incremental: e.key !== 'Enter',
            });
        });

        this.element.appendChild(this.input);
        this.element.appendChild(prevBtn);
        this.element.appendChild(nextBtn);
        this.element.appendChild(closeBtn);

        parent.appendChild(this.element);

        this.focus();
    }

    hide(): void {
        if (this.element) {
            this.element.style.visibility = 'hidden';
        }
    }

    private focus(): void {
        this.input?.focus();
        this.input?.select();
    }

    private createButton(svgHTML: string): HTMLButtonElement {
        const btn = document.createElement('button');

        btn.style.display = 'flex';
        btn.style.alignItems = 'center';
        btn.style.justifyContent = 'center';
        btn.style.width = '26px';
        btn.style.height = '26px';
        btn.style.background = 'transparent';
        btn.style.border = '1px solid rgb(var(--color-600))';
        btn.style.borderRadius = '6px';
        btn.style.cursor = 'pointer';
        btn.style.color = 'rgb(var(--color-200))';
        btn.style.padding = '0';

        btn.innerHTML = svgHTML;

        const svg = btn.querySelector('svg') as SVGElement;
        if (svg) {
            svg.style.width = '16px';
            svg.style.height = '16px';
        }

        return btn;
    }
}
