import React from 'react';
import classNames from 'classnames';

const ContentContainer = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
    <div className={classNames('mx-4 max-w-[1200px] xl:mx-auto', className)} {...props} />
);

export default ContentContainer;
