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

const Block = (props: React.ComponentProps<typeof Button>) => <Button className='mr-2 h-10 w-10 p-0' {...props} />;

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
                <div className='mt-4 flex justify-center'>
                    {(pages?.[0] ?? 0) > 1 && !isFirstPage && (
                        <Block isSecondary color={'primary'} onClick={() => onPageSelect(1)}>
                            <FaAnglesLeft />
                        </Block>
                    )}
                    {pages.map((i) => (
                        <Block
                            isSecondary={pagination.currentPage !== i}
                            color={'primary'}
                            key={`block_page_${i}`}
                            onClick={() => onPageSelect(i)}
                        >
                            {i}
                        </Block>
                    ))}
                    {(pages?.[4] ?? 0) < pagination.totalPages && !isLastPage && (
                        <Block isSecondary color={'primary'} onClick={() => onPageSelect(pagination.totalPages)}>
                            <FaAnglesRight />
                        </Block>
                    )}
                </div>
            )}
        </>
    );
}

export default Pagination;
