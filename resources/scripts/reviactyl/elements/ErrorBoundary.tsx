import React from 'react';
import tw from 'twin.macro';
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
            <div css={tw`flex items-center justify-center w-full my-4`}>
                <div css={tw`flex items-center bg-gray-900 border border-gray-800 rounded-ui p-3 text-red-500`}>
                    <Icon icon={FaTriangleExclamation} css={tw`h-4 w-auto mr-2`} />
                    <p css={tw`text-sm text-gray-100`}>
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
