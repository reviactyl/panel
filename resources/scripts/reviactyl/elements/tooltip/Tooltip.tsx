import React, { cloneElement, useRef, useState } from 'react';
import {
    arrow,
    autoUpdate,
    flip,
    offset,
    shift,
    useFloating,
    useHover,
    useFocus,
    useClick,
    useDismiss,
    useRole,
    useInteractions,
    Placement,
    Side,
} from '@floating-ui/react';
import { AnimatePresence, motion } from 'framer-motion';
import classNames from 'classnames';

type Interaction = 'hover' | 'click' | 'focus';

interface Props {
    rest?: number;
    delay?: number | Partial<{ open: number; close: number }>;
    content: string | React.ReactNode;
    disabled?: boolean;
    arrow?: boolean;
    interactions?: Interaction[];
    placement?: Placement;
    className?: string;
    children: React.ReactElement;
}

const arrowSides: Record<Side, string> = {
    top: 'bottom-[-6px] left-0',
    bottom: 'top-[-6px] left-0',
    right: 'top-0 left-[-6px]',
    left: 'top-0 right-[-6px]',
};

export default ({ children, ...props }: Props) => {
    const arrowEl = useRef<HTMLDivElement | null>(null);
    const [open, setOpen] = useState(false);

    const { x, y, refs, middlewareData, strategy, context } = useFloating({
        open,
        onOpenChange: setOpen,
        placement: props.placement || 'top',
        strategy: 'fixed',
        middleware: [
            offset(props.arrow ? 10 : 6),
            flip(),
            shift({ padding: 6 }),
            arrow({ element: arrowEl, padding: 6 }),
        ],
        whileElementsMounted: autoUpdate,
    });

    const interactions = props.interactions || ['hover', 'focus'];

    const { getReferenceProps, getFloatingProps } = useInteractions([
        useHover(context, {
            restMs: props.rest ?? 30,
            delay: props.delay ?? 0,
            enabled: interactions.includes('hover'),
        }),
        useFocus(context, { enabled: interactions.includes('focus') }),
        useClick(context, { enabled: interactions.includes('click') }),
        useRole(context, { role: 'tooltip' }),
        useDismiss(context),
    ]);

    const side = arrowSides[(props.placement || 'top').split('-')[0] as Side];
    const { x: ax = 0, y: ay = 0 } = middlewareData.arrow || {};

    if (props.disabled) {
        return children;
    }

    const childProps = (children.props || {}) as Record<string, any>;

    return (
        <>
            {cloneElement(
                children,
                getReferenceProps({
                    ...childProps,
                    ref: refs.setReference,
                }) as any
            )}

            <AnimatePresence>
                {open && (
                    <motion.div
                        initial={{ opacity: 0, scale: 0.85 }}
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0 }}
                        transition={
                            {
                                type: 'spring',
                                damping: 20,
                                stiffness: 300,
                                duration: 0.075,
                            } as any
                        }
                        {...getFloatingProps({
                            ref: refs.setFloating,
                            className: classNames(
                                'bg-gray-900 text-sm text-gray-200 px-3 py-2 rounded-ui border border-gray-800 pointer-events-none max-w-[24rem] z-50',
                                props.className
                            ),
                            style: {
                                position: strategy,
                                top: y ?? 0,
                                left: x ?? 0,
                            },
                        })}
                    >
                        {props.content}

                        {props.arrow && (
                            <div
                                ref={arrowEl}
                                style={{
                                    position: 'absolute',
                                    left: ax ?? 0,
                                    top: ay ?? 0,
                                    transform: 'rotate(45deg)',
                                }}
                                className={classNames('bg-gray-950 w-3 h-3', 'absolute', side)}
                            />
                        )}
                    </motion.div>
                )}
            </AnimatePresence>
        </>
    );
};
