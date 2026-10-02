import React from 'react';
import classNames from 'classnames';
import { FaCircleInfo, FaTriangleExclamation } from 'react-icons/fa6';

interface AlertProps {
    type: 'warning' | 'danger';
    className?: string;
    children: React.ReactNode;
}

export default ({ type, className, children }: AlertProps) => {
    return (
        <div
            className={classNames(
                'flex items-center border text-gray-50 rounded-ui shadow px-4 py-3 backdrop-blur-md',
                {
                    ['border-danger/30 bg-danger/20']: type === 'danger',
                    ['border-yellow-500/30 bg-yellow-500/20']: type === 'warning',
                },
                className,
            )}
        >
            {type === 'danger' ? (
                <FaTriangleExclamation className={'w-5 h-5 text-red-400 mr-2'} />
            ) : (
                <FaCircleInfo className={'w-5 h-5 text-yellow-500 mr-2'} />
            )}
            {children}
        </div>
    );
};
