import type { ReactNode } from 'react';
import { Fragment, useEffect, useMemo, useRef, useState } from 'react';
import Spinner from '@/components/elements/Spinner';
import tw from 'twin.macro';
import styled, { css } from 'styled-components';
import { breakpoint } from '@/theme';
import FadeTransition from '@/components/elements/transitions/FadeTransition';
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

export const ModalMask = styled.div`
    ${tw`fixed z-50 overflow-auto flex w-full inset-0`};
`;

const ModalContainer = styled.div<{ alignTop?: boolean; size?: 'sm' | 'md' | 'lg' }>`
    max-width: 95%;
    max-height: calc(100vh - 8rem);

    ${(props) =>
        props.size === 'sm'
            ? css`
                  ${breakpoint('md')`max-width: 50%`};
                  ${breakpoint('lg')`max-width: 35%`};
              `
            : props.size === 'lg'
            ? css`
                  ${breakpoint('md')`max-width: 90%`};
                  ${breakpoint('lg')`max-width: 80%`};
              `
            : css`
                  ${breakpoint('md')`max-width: 75%`};
                  ${breakpoint('lg')`max-width: 50%`};
              `};

    ${tw`relative flex flex-col w-full m-auto`};

    ${(props) =>
        props.alignTop &&
        css`
            margin-top: 20%;
            ${breakpoint('md')`margin-top: 10%`};
        `};

    margin-bottom: auto;

    & > .close-icon {
        ${tw`absolute right-0 top-0 p-2 m-2 text-gray-200 cursor-pointer opacity-70 transition-all duration-150 ease-linear hover:opacity-100`};

        &:hover {
            ${tw`transform rotate-90`}
        }

        & > svg {
            ${tw`w-6 h-6`};
        }
    }
`;

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
                className='bg-gray-800/40 backdrop-blur-sm transition-all duration-300 ease-in-out'
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
                <ModalContainer alignTop={top} size={size}>
                    {isDismissable && (
                        <div
                            className={'close-icon'}
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
                            css={tw`absolute w-full h-full rounded flex items-center justify-center`}
                            style={{ background: 'hsla(211, 10%, 53%, 0.35)', zIndex: 9999 }}
                        >
                            <Spinner />
                        </div>
                    </FadeTransition>
                    <div
                        css={[
                            tw`bg-gray-700 border border-gray-600 p-3 sm:p-4 md:p-6 rounded-ui shadow-md transition-all duration-150`,
                            noScroll ? tw`overflow-visible` : tw`overflow-y-scroll`,
                        ]}
                    >
                        {children}
                    </div>
                </ModalContainer>
            </ModalMask>
        </FadeTransition>
    );
}

function PortaledModal({ children, ...props }: ModalProps) {
    const element = useRef(document.getElementById('modal-portal'));

    return createPortal(<Modal {...props}>{children}</Modal>, element.current!);
}

export default PortaledModal;
