import React from 'react';
import classNames from 'classnames';

interface Props {
    title: React.ReactNode;
    children?: React.ReactNode;
    className?: string;
}

const PageHeader = ({ title, children, className }: Props) => (
    <header
        className={classNames(
            'mb-6 flex flex-wrap items-center justify-between gap-4 rounded-ui border border-gray-800 bg-gray-900 p-4 sm:p-5',
            className,
        )}
    >
        <h1 className='min-w-0 text-2xl font-semibold leading-tight text-gray-50 wrap-break-word'>{title}</h1>
        {children}
    </header>
);

export default PageHeader;
