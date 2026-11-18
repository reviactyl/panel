import React from 'react';
import { PaginatedResult } from '@/api/http';
import Button from '@/reviactyl/elements/Button';
import { FaAnglesLeft, FaAnglesRight } from 'react-icons/fa6';

interface RenderFuncProps<T> {
    items: T[];
    isLastPage: boolean;
    isFirstPage: boolean;
}

interface Props<T> {
    data: PaginatedResult<T>;
    showGoToLast?: boolean;
    showGoToFirst?: boolean;
    onPageSelect: (page: number) => void;
    children: (props: RenderFuncProps<T>) => React.ReactNode;
}

const Block = (props: React.ComponentProps<typeof Button>) => (
    <Button type='button' className='h-10 min-w-10 shrink-0 px-2 py-0' {...props} />
);

function Pagination<T>({ data: { items, pagination }, onPageSelect, children }: Props<T>) {
    const isFirstPage = pagination.currentPage === 1;
    const isLastPage = pagination.currentPage >= pagination.totalPages;

    const pages = [];

    // Start two spaces before the current page. If that puts us before the starting page default
    // to the first page as the starting point.
    const start = Math.max(pagination.currentPage - 2, 1);
    const end = Math.min(pagination.totalPages, pagination.currentPage + 5);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return (
        <>
            {children({ items, isFirstPage, isLastPage })}
            {pages.length > 1 && (
                <div className='mt-4 flex flex-wrap justify-center gap-2'>
                    {(pages?.[0] ?? 0) > 1 && !isFirstPage && (
                        <Block isSecondary color={'primary'} aria-label='1' onClick={() => onPageSelect(1)}>
                            <FaAnglesLeft className='rtl:rotate-180' />
                        </Block>
                    )}
                    {pages.map((i) => (
                        <Block
                            isSecondary={pagination.currentPage !== i}
                            color={'primary'}
                            key={`block_page_${i}`}
                            aria-current={pagination.currentPage === i ? 'page' : undefined}
                            onClick={() => onPageSelect(i)}
                        >
                            {i}
                        </Block>
                    ))}
                    {(pages[pages.length - 1] ?? 0) < pagination.totalPages && !isLastPage && (
                        <Block
                            isSecondary
                            color={'primary'}
                            aria-label={String(pagination.totalPages)}
                            onClick={() => onPageSelect(pagination.totalPages)}
                        >
                            <FaAnglesRight className='rtl:rotate-180' />
                        </Block>
                    )}
                </div>
            )}
        </>
    );
}

export default Pagination;
