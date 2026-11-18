import { PaginationDataSet } from '@/api/http';
import classNames from 'classnames';
import { Button } from '@/reviactyl/components/button/index';
import { ChevronDoubleLeftIcon, ChevronDoubleRightIcon } from '@heroicons/react/solid';

interface Props {
    className?: string;
    pagination: PaginationDataSet;
    onPageSelect: (page: number) => void;
}

const PaginationFooter = ({ pagination, className, onPageSelect }: Props) => {
    const start = (pagination.currentPage - 1) * pagination.perPage;
    const end = (pagination.currentPage - 1) * pagination.perPage + pagination.count;

    const { currentPage: current, totalPages: total } = pagination;

    const pages = { previous: [] as number[], next: [] as number[] };
    for (let i = 1; i <= 2; i++) {
        if (current - i >= 1) {
            pages.previous.push(current - i);
        }
        if (current + i <= total) {
            pages.next.push(current + i);
        }
    }

    if (pagination.total === 0) {
        return null;
    }

    const buttonProps = (page: number) => ({
        size: Button.Sizes.Small,
        type: 'button' as const,
        className: 'min-w-8 shrink-0',
        variant: Button.Variants.Secondary,
        onClick: () => onPageSelect(page),
    });

    return (
        <div className={classNames('flex flex-wrap items-center justify-between gap-3 my-2', className)}>
            <p className={'text-sm text-muted'}>
                Showing&nbsp;
                <span className={'font-semibold'}>{Math.max(start, Math.min(pagination.total, 1))}</span>
                &nbsp;to&nbsp;
                <span className={'font-semibold'}>{end}</span> of&nbsp;
                <span className={'font-semibold'}>{pagination.total}</span> results.
            </p>
            {pagination.totalPages > 1 && (
                <div className={'flex flex-wrap gap-1'}>
                    <Button.Text {...buttonProps(1)} aria-label='1' disabled={current <= 1}>
                        <ChevronDoubleLeftIcon className={'w-3 h-3'} />
                    </Button.Text>
                    {pages.previous.reverse().map((value) => (
                        <Button.Text key={`previous-${value}`} {...buttonProps(value)}>
                            {value}
                        </Button.Text>
                    ))}
                    <Button type='button' size={Button.Sizes.Small} className='min-w-8 shrink-0' aria-current='page'>
                        {current}
                    </Button>
                    {pages.next.map((value) => (
                        <Button.Text key={`next-${value}`} {...buttonProps(value)}>
                            {value}
                        </Button.Text>
                    ))}
                    <Button.Text {...buttonProps(total)} aria-label={String(total)} disabled={current >= total}>
                        <ChevronDoubleRightIcon className={'w-3 h-3'} />
                    </Button.Text>
                </div>
            )}
        </div>
    );
};

export default PaginationFooter;
