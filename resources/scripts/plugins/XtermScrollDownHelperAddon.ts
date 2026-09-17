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
            this.element.style.opacity = '1';
            this.element.style.transform = 'translateY(0)';
            this.element.style.pointerEvents = 'auto';
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
        this.element.style.border = '1.5px solid rgb(var(--color-700))';
        this.element.style.boxShadow = '0 4px 24px rgba(var(--color-primary), 0.3)';
        this.element.style.backdropFilter = 'blur(8px)';
        this.element.style.backgroundColor = 'rgb(var(--color-800))';
        this.element.style.color = 'rgb(var(--color-200))';
        this.element.style.borderRadius = '15px';
        this.element.style.zIndex = '999';
        this.element.style.cursor = 'pointer';
        this.element.style.opacity = '0';
        this.element.style.transform = 'translateY(8px)';
        this.element.style.transition = 'opacity 0.2s ease, transform 0.2s ease';

        this.element.addEventListener('click', () => {
            this.terminal.scrollToBottom();
        });

        this.terminal.element.appendChild(this.element);

        requestAnimationFrame(() => {
            if (this.element) {
                this.element.style.opacity = '1';
                this.element.style.transform = 'translateY(0)';
                this.element.style.pointerEvents = 'auto';
            }
        });
    }

    hide(): void {
        if (this.element) {
            this.element.style.opacity = '0';
            this.element.style.transform = 'translateY(8px)';
            this.element.style.pointerEvents = 'none';
        }
    }

    isScrolledDown(): boolean {
        return this.terminal.buffer.active.viewportY === this.terminal.buffer.active.baseY;
    }
}
