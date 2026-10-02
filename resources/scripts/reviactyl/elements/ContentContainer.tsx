import React from 'react';
import classNames from 'classnames';

const ContentContainer = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
    <div className={classNames('mx-4 max-w-300 my-4 xl:mx-auto', className)} {...props} />
);

export default ContentContainer;
