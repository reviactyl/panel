import type { SearchAddon } from '@xterm/addon-search';
import type { Terminal } from '@xterm/xterm';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { SearchBarAddon } from './XtermSearchBarAddon';

describe('console search', () => {
    let parent: HTMLDivElement;
    let terminalInput: HTMLTextAreaElement;
    let input: HTMLInputElement;
    let bar: HTMLDivElement;
    let addon: SearchBarAddon;
    const findNext = vi.fn();
    const findPrevious = vi.fn();

    beforeEach(() => {
        vi.clearAllMocks();
        parent = document.createElement('div');
        const element = document.createElement('div');
        terminalInput = document.createElement('textarea');
        element.appendChild(terminalInput);
        parent.appendChild(element);
        document.body.appendChild(parent);
        addon = new SearchBarAddon({ findNext, findPrevious } as unknown as SearchAddon);
        addon.activate({ element, focus: () => terminalInput.focus() } as unknown as Terminal);
        addon.show();
        input = parent.querySelector('input')!;
        bar = input.parentElement as HTMLDivElement;
    });

    afterEach(() => {
        addon.dispose();
        parent.remove();
    });

    it('closes focused search on Escape without searching and returns focus to the terminal', () => {
        expect(document.activeElement).toBe(input);
        input.value = 'hello';
        input.dispatchEvent(new KeyboardEvent('keyup', { key: 'o', bubbles: true }));
        findNext.mockClear();

        input.dispatchEvent(new KeyboardEvent('keyup', { key: 'Escape', bubbles: true }));

        expect(bar.style.visibility).toBe('hidden');
        expect(findNext).not.toHaveBeenCalled();
        expect(findPrevious).not.toHaveBeenCalled();
        expect(document.activeElement).toBe(terminalInput);

        addon.show();
        expect(bar.style.visibility).toBe('visible');
        expect(document.activeElement).toBe(input);
        expect(input.value).toBe('hello');
    });

    it('preserves incremental search, Enter, and previous/next controls', () => {
        input.value = 'hello';
        input.dispatchEvent(new KeyboardEvent('keyup', { key: 'o', bubbles: true }));
        expect(findNext).toHaveBeenLastCalledWith('hello', { incremental: true });

        input.dispatchEvent(new KeyboardEvent('keyup', { key: 'Enter', bubbles: true }));
        expect(findNext).toHaveBeenLastCalledWith('hello', { incremental: false });
        expect(findNext).toHaveBeenCalledTimes(2);

        const [previous, next] = bar.querySelectorAll('button');
        previous!.click();
        expect(findPrevious).toHaveBeenCalledExactlyOnceWith('hello', { incremental: false });
        next!.click();
        expect(findNext).toHaveBeenLastCalledWith('hello', { incremental: false });
        expect(findNext).toHaveBeenCalledTimes(3);
        expect(bar.style.visibility).not.toBe('hidden');
    });
});
