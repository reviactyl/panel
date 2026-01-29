import React from 'react';

interface CodeProps {
    dark?: boolean;
    children: React.ReactNode;
}

export default ({ dark, children }: CodeProps) => {
    void dark; // legacy prop, we actually dont need it. 

    return (
        <code className="font-mono text-sm px-2 py-1 inline-block rounded-ui bg-gray-800 border border-gray-600">
            {children}
        </code>
    );
};