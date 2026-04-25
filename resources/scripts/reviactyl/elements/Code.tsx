import React from 'react';

interface CodeProps {
    children: React.ReactNode;
}

export default ({ children }: CodeProps) => (
    <code className={'font-mono text-sm px-2 py-1 inline-block rounded-ui bg-gray-800 border border-gray-600'}>
        {children}
    </code>
);
