import type { HTMLAttributes, ReactNode } from 'react';
import { Fragment, forwardRef, useCallback, useEffect, useMemo, useState } from 'react';
import Spinner from '@/reviactyl/elements/Spinner';
import FadeTransition from '@/reviactyl/elements/transitions/FadeTransition';
import { Dialog } from '@headlessui/react';
import { useTranslation } from 'react-i18next';

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
    <div className={`fixed inset-0 z-9999 flex w-full overflow-auto ${className || ''}`} {...props} />
);
const sizes = {
    sm: 'md:max-w-[50%] lg:max-w-[35%]',
    md: 'md:max-w-[75%] lg:max-w-[50%]',
    lg: 'md:max-w-[90%] lg:max-w-[80%]',
};

const ModalDialog = forwardRef<HTMLDivElement, Omit<HTMLAttributes<HTMLDivElement>, 'role'> & { onEscape: () => void }>(
    function ModalDialog({ children, onEscape, ...props }, ref) {
        const [title, setTitle] = useState('');
        const [ready, setReady] = useState(false);
        const setDialog = useCallback(
            (node: HTMLDivElement | null) => {
                if (typeof ref === 'function') ref(node);
                else if (ref) ref.current = node;
                // Activate after the portal mounts so background inertness includes the page.
                if (node) setReady(true);
                const heading = node?.querySelector('h1, h2, h3, h4, h5, h6');
                if (heading) {
                    setTitle(heading.textContent || '');
                }
            },
            [ref],
        );

        return (
            <Dialog
                {...props}
                ref={setDialog}
                className={`fixed inset-0 z-9999 ${props.className || ''}`}
                static
                open={ready}
                onClose={() => {}}
                autoFocus={false}
                onKeyDown={(event) => {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        event.stopPropagation();
                        onEscape();
                    }
                }}
            >
                <Dialog.Title as='span' className='sr-only'>
                    {title}
                </Dialog.Title>
                {children}
            </Dialog>
        );
    },
);

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
    const { t } = useTranslation('strings');

    const isDismissable = useMemo(() => {
        return (dismissable ?? true) && !(showSpinnerOverlay || false);
    }, [dismissable, showSpinnerOverlay]);

    useEffect(() => {
        setRender(visible);
    }, [visible]);

    return (
        <FadeTransition
            as={Fragment}
            show={render}
            duration='duration-150'
            appear={appear ?? true}
            unmount
            afterLeave={onDismissed}
        >
            <ModalDialog
                onEscape={() => {
                    if (isDismissable && closeOnEscape) setRender(false);
                }}
            >
                <ModalMask
                    className='bg-gray-900/40 backdrop-blur-xs transition-all duration-300 ease-in-out'
                    onClick={(e: React.MouseEvent) => e.stopPropagation()}
                    onContextMenu={(e: React.MouseEvent) => e.stopPropagation()}
                    onMouseDown={(e: React.MouseEvent) => {
                        if (isDismissable && closeOnBackground) {
                            e.stopPropagation();
                            if (e.target === e.currentTarget) {
                                setRender(false);
                            }
                        }
                    }}
                >
                    <Dialog.Panel
                        className={`relative m-auto mb-auto flex max-h-[calc(100vh-8rem)] w-full max-w-[95%] flex-col ${
                            sizes[size]
                        } ${top ? 'mt-[20%] md:mt-[10%]' : ''}`}
                    >
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
                        {isDismissable && (
                            <button
                                type='button'
                                aria-label={t('close')}
                                className='absolute -top-10 right-0 cursor-pointer p-2 text-white opacity-50 transition-all duration-150 ease-linear hover:rotate-90 hover:opacity-100 [&>svg]:h-6 [&>svg]:w-6'
                                onClick={() => setRender(false)}
                            >
                                <svg
                                    xmlns='http://www.w3.org/2000/svg'
                                    fill='none'
                                    viewBox='0 0 24 24'
                                    stroke='currentColor'
                                    aria-hidden
                                >
                                    <path
                                        strokeLinecap='round'
                                        strokeLinejoin='round'
                                        strokeWidth='2'
                                        d='M6 18L18 6M6 6l12 12'
                                    />
                                </svg>
                            </button>
                        )}
                    </Dialog.Panel>
                </ModalMask>
            </ModalDialog>
        </FadeTransition>
    );
}

export default Modal;
