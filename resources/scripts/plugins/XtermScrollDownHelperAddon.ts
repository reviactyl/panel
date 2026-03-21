import { Terminal, ITerminalAddon } from '@xterm/xterm';

export class ScrollDownHelperAddon implements ITerminalAddon {
    private terminal: Terminal = new Terminal();
    private element?: HTMLDivElement;

    activate(terminal: Terminal): void {
        this.terminal = terminal;

        this.terminal.onScroll(() => {
            if (this.isScrolledDown()) {
                this.hide();
                return;
            }

            this.show();
        });

        this.terminal.onLineFeed(() => {
            if (!this.isScrolledDown()) {
                this.show();
            }
        });

        // Addon activation can happen before terminal.open(), so defer initial mount.
        requestAnimationFrame(() => {
            if (this.isScrolledDown()) {
                this.hide();
                return;
            }

            this.show();
        });
    }

    dispose(): void {
        // ignore
    }

    show(): void {
        if (!this.terminal || !this.terminal.element) {
            return;
        }
        if (this.element) {
            this.element.style.visibility = 'visible';
            return;
        }

        this.terminal.element.style.position = 'relative';

        this.element = document.createElement('div');
        this.element.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" /></svg>';
        this.element.style.position = 'absolute';
        this.element.style.right = '1.5rem';
        this.element.style.bottom = '.5rem';
        this.element.style.padding = '.5rem';
        this.element.style.fontSize = '1.25em';
        this.element.style.border = '1.5px solid rgb(var(--color-600))';
        this.element.style.boxShadow = '0 2px 8px rgb(var(--color-primary))';
        this.element.style.backgroundColor = 'rgb(var(--color-700))';
        this.element.style.color = 'rgb(var(--color-200))';
        this.element.style.borderRadius = '15px';
        this.element.style.zIndex = '999';
        this.element.style.cursor = 'pointer';

        this.element.addEventListener('click', () => {
            this.terminal.scrollToBottom();
        });

        this.terminal.element.appendChild(this.element);
    }

    hide(): void {
        if (this.element) {
            this.element.style.visibility = 'hidden';
        }
    }

    isScrolledDown(): boolean {
        return this.terminal.buffer.active.viewportY === this.terminal.buffer.active.baseY;
    }
}
