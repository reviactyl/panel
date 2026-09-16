import React from 'react';

export const LogoContainer = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
    <div className={`flex gap-x-2 pb-5 ${className || ''}`} {...props} />
);
