import React from 'react';

const SubNavigation = ({ children, className = '', ...props }: React.HTMLAttributes<HTMLDivElement>) => (
    <div
        className={`w-full overflow-x-auto bg-gray-900 shadow [&>div]:mx-auto [&>div]:flex [&>div]:max-w-[1200px] [&>div]:items-center [&>div]:px-2 [&>div]:text-sm [&>div>a]:inline-block [&>div>a]:whitespace-nowrap [&>div>a]:px-4 [&>div>a]:py-3 [&>div>a]:text-gray-300 [&>div>a]:no-underline [&>div>a]:transition-all [&>div>a]:duration-150 [&>div>div]:inline-block [&>div>div]:whitespace-nowrap [&>div>div]:px-4 [&>div>div]:py-3 [&>div>div]:text-gray-300 [&>div>div]:no-underline [&>div>div]:transition-all [&>div>div]:duration-150 [&>div>*:not(:first-of-type)]:ml-2 [&>div>a:hover]:text-gray-100 [&>div>div:hover]:text-gray-100 [&>div>a:active]:text-gray-100 [&>div>a.active]:text-gray-100 [&>div>div:active]:text-gray-100 [&>div>div.active]:text-gray-100 ${className}`}
        {...props}
    >
        {children}
    </div>
);

export default SubNavigation;
