import type { ReactNode } from 'react';
import { Fragment, useEffect, useMemo, useRef, useState } from 'react';
import Spinner from '@/reviactyl/elements/Spinner';
import FadeTransition from '@/reviactyl/elements/transitions/FadeTransition';
import { createPortal } from 'react-dom';

export interface RequiredModalProps {
    children?: ReactNode;
    visible: boolean;
    onDismissed: () => void;
    appear?: boolean;
    top?: boolean;
}

export interface ModalProps extends RequiredModalProps {
    dismissable?: boolean;
    closeOnEscape?: boolean;
    closeOnBackground?: boolean;
    showSpinnerOverlay?: boolean;
    size?: 'sm' | 'md' | 'lg';
    noScroll?: boolean;
}

export const ModalMask = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
    <div className={`fixed inset-0 z-[9999] flex w-full overflow-auto ${className || ''}`} {...props} />
);
const sizes = {
    sm: 'md:max-w-[50%] lg:max-w-[35%]',
    md: 'md:max-w-[75%] lg:max-w-[50%]',
    lg: 'md:max-w-[90%] lg:max-w-[80%]',
};

function Modal({
    visible,
    appear,
    dismissable,
    showSpinnerOverlay,
    top = true,
    closeOnBackground = true,
    closeOnEscape = true,
    onDismissed,
    size = 'md',
    noScroll = false,
    children,
}: ModalProps) {
    const [render, setRender] = useState(visible);

    const isDismissable = useMemo(() => {
        return (dismissable || true) && !(showSpinnerOverlay || false);
    }, [dismissable, showSpinnerOverlay]);

    useEffect(() => {
        if (!isDismissable || !closeOnEscape) return;

        const handler = (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                setRender(false);
                onDismissed();
            }
        };

        window.addEventListener('keydown', handler);
        return () => {
            window.removeEventListener('keydown', handler);
        };
    }, [isDismissable, closeOnEscape, render]);

    useEffect(() => {
        setRender(visible);
    }, [visible]);

    return (
        <FadeTransition as={Fragment} show={render} duration='duration-150' appear={appear ?? true} unmount>
            <ModalMask
                className='bg-gray-900/40 backdrop-blur-sm-xs transition-all duration-300 ease-in-out'
                onClick={(e: React.MouseEvent) => e.stopPropagation()}
                onContextMenu={(e: React.MouseEvent) => e.stopPropagation()}
                onMouseDown={(e: React.MouseEvent) => {
                    if (isDismissable && closeOnBackground) {
                        e.stopPropagation();
                        if (e.target === e.currentTarget) {
                            setRender(false);
                            onDismissed();
                        }
                    }
                }}
            >
                <div
                    className={`relative m-auto mb-auto flex max-h-[calc(100vh-8rem)] w-full max-w-[95%] flex-col ${
                        sizes[size]
                    } ${top ? 'mt-[20%] md:mt-[10%]' : ''}`}
                >
                    {isDismissable && (
                        <div
                            className='absolute -top-10 right-0 cursor-pointer p-2 text-white opacity-50 transition-all duration-150 ease-linear hover:rotate-90 hover:opacity-100 [&>svg]:h-6 [&>svg]:w-6'
                            onClick={() => {
                                setRender(false);
                                onDismissed();
                            }}
                        >
                            <svg
                                xmlns={'http://www.w3.org/2000/svg'}
                                fill={'none'}
                                viewBox={'0 0 24 24'}
                                stroke={'currentColor'}
                            >
                                <path
                                    strokeLinecap={'round'}
                                    strokeLinejoin={'round'}
                                    strokeWidth={'2'}
                                    d={'M6 18L18 6M6 6l12 12'}
                                />
                            </svg>
                        </div>
                    )}
                    <FadeTransition duration='duration-150' show={showSpinnerOverlay ?? false} appear>
                        <div
                            className='absolute flex h-full w-full items-center justify-center rounded'
                            style={{ background: 'hsla(211, 10%, 53%, 0.35)', zIndex: 9999 }}
                        >
                            <Spinner />
                        </div>
                    </FadeTransition>
                    <div
                        className={`rounded-ui border border-gray-800 bg-gray-900 p-3 shadow-md transition-all duration-150 sm:p-4 md:p-6 ${
                            noScroll ? 'overflow-visible' : 'overflow-y-scroll'
                        }`}
                    >
                        {children}
                    </div>
                </div>
            </ModalMask>
        </FadeTransition>
    );
}

function PortaledModal({ children, ...props }: ModalProps) {
    const element = useRef(document.getElementById('modal-portal'));

    return createPortal(<Modal {...props}>{children}</Modal>, element.current!);
}

export default PortaledModal;
