import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import Pagination from './Pagination';
import PaginationFooter from './table/PaginationFooter';

let container: HTMLDivElement;
let root: Root;
const onPageSelect = vi.fn();
const pagination = (currentPage: number, totalPages: number) => ({
    currentPage,
    totalPages,
    total: totalPages * 20,
    count: 20,
    perPage: 20,
});

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    container = document.createElement('div');
    root = createRoot(container);
    onPageSelect.mockReset();
});
afterEach(async () => {
    await act(async () => root.unmount());
});

it.each([
    [1, 2],
    [2, 7],
    [96, 101],
    [101, 101],
])('does not repeat endpoints already in the numbered range (%s of %s)', async (current, total) => {
    await act(async () =>
        root.render(
            <Pagination data={{ items: ['row'], pagination: pagination(current!, total!) }} onPageSelect={onPageSelect}>
                {({ items, isFirstPage, isLastPage }) => <span>{`${items[0]}:${isFirstPage}:${isLastPage}`}</span>}
            </Pagination>,
        ),
    );
    const shortcuts = container.querySelectorAll<HTMLButtonElement>('button:has(svg)');
    const expected = current! > 3 ? [1] : [];
    for (const shortcut of shortcuts) await act(async () => shortcut.click());
    expect(onPageSelect.mock.calls.map(([page]) => page)).toEqual(expected);
    expect(container.textContent).toContain(`row:${current === 1}:${current === total}`);
});

it('keeps both endpoint shortcuts and numbered selection working when needed', async () => {
    await act(async () =>
        root.render(
            <Pagination data={{ items: [], pagination: pagination(50, 101) }} onPageSelect={onPageSelect}>
                {() => null}
            </Pagination>,
        ),
    );
    const buttons = Array.from(container.querySelectorAll('button'));
    await act(async () => buttons[0]!.click());
    await act(async () => buttons.at(-1)!.click());
    await act(async () => buttons.find((button) => button.textContent === '51')!.click());
    expect(onPageSelect.mock.calls.map(([page]) => page)).toEqual([1, 101, 51]);
});

it.each([1, 2, 100, 101])('enables table jumps unless already at the endpoint (page %s)', async (current) => {
    await act(async () =>
        root.render(<PaginationFooter pagination={pagination(current, 101)} onPageSelect={onPageSelect} />),
    );
    const jumps = container.querySelectorAll<HTMLButtonElement>('button:has(svg)');
    expect(jumps[0]!.disabled).toBe(current === 1);
    expect(jumps[1]!.disabled).toBe(current === 101);
    await act(async () => jumps[0]!.click());
    await act(async () => jumps[1]!.click());
    expect(onPageSelect.mock.calls.map(([page]) => page)).toEqual(
        [current !== 1 ? 1 : null, current !== 101 ? 101 : null].filter((page) => page !== null),
    );
});

it.each([0, 1])('hides unnecessary pagination for %s pages', async (total) => {
    await act(async () =>
        root.render(
            <Pagination data={{ items: [], pagination: pagination(1, total) }} onPageSelect={onPageSelect}>
                {() => <span>Content</span>}
            </Pagination>,
        ),
    );
    expect(container.textContent).toBe('Content');
    expect(container.querySelector('button')).toBeNull();
});
