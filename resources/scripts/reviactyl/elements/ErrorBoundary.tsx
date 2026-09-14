import React from 'react';
import Icon from '@/reviactyl/elements/Icon';
import { FaTriangleExclamation } from 'react-icons/fa6';

interface State {
    hasError: boolean;
}

interface ErrorBoundaryProps {
    children?: React.ReactNode;
}

class ErrorBoundary extends React.Component<ErrorBoundaryProps, State> {
    override state: State = {
        hasError: false,
    };

    static getDerivedStateFromError() {
        return { hasError: true };
    }

    override componentDidCatch(error: Error) {
        console.error(error);
    }

    override render() {
        return this.state.hasError ? (
            <div className='my-4 flex w-full items-center justify-center'>
                <div className='flex items-center rounded-ui border border-gray-800 bg-gray-900 p-3 text-red-500'>
                    <Icon icon={FaTriangleExclamation} className='mr-2 h-4 w-auto' />
                    <p className='text-sm text-gray-100'>
                        An error was encountered by the application while rendering this view. Try refreshing the page.
                    </p>
                </div>
            </div>
        ) : (
            this.props.children
        );
    }
}

export default ErrorBoundary;
