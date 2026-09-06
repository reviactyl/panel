import React from 'react';
import classNames from 'classnames';

export const StatBlock = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
    <div className={classNames('flex items-center gap-2 rounded-ui border px-4 py-2', className)} {...props} />
);
