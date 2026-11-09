import React, { useEffect, useRef, useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import Portal from '@/reviactyl/elements/Portal';
import copy from 'copy-to-clipboard';
import classNames from 'classnames';
import Blur from '@/reviactyl/ui/Blur';

interface CopyOnClickProps {
    text: string | number | null | undefined;
    showInNotification?: boolean;
    children: React.ReactNode;
}

interface Notification {
    id: number;
    text: string;
}

const CopyOnClick = ({ text, showInNotification = true, children }: CopyOnClickProps) => {
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const timeouts = useRef<Map<number, ReturnType<typeof setTimeout>>>(new Map());

    useEffect(() => {
        return () => {
            timeouts.current.forEach(clearTimeout);
            timeouts.current.clear();
        };
    }, []);

    if (!React.isValidElement<React.HTMLAttributes<HTMLElement>>(children)) {
        throw new Error('Component passed to <CopyOnClick/> must be a valid React element.');
    }

    const keyboardAction =
        (children.type === Blur ||
            (typeof children.type === 'string' && ['p', 'span', 'div', 'pre', 'code'].includes(children.type))) &&
        !children.props.contentEditable;

    const child = !text
        ? React.Children.only(children)
        : React.cloneElement(React.Children.only(children), {
              ...(keyboardAction && {
                  role: 'button',
                  tabIndex: children.props.tabIndex ?? 0,
                  onKeyDown: (e: React.KeyboardEvent<HTMLElement>) => {
                      children.props.onKeyDown?.(e);
                      if (e.target !== e.currentTarget || e.defaultPrevented) {
                          return;
                      }
                      if (e.key === 'Enter' || e.key === ' ') {
                          e.preventDefault();
                          if (!e.repeat) {
                              e.currentTarget.click();
                          }
                      }
                  },
              }),
              className: classNames(children.props.className, 'cursor-pointer', {
                  'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary':
                      keyboardAction,
              }),
              onClick: (e: React.MouseEvent<HTMLElement>) => {
                  copy(String(text));

                  const id = Date.now() + Math.random();
                  const notification: Notification = {
                      id,
                      text: showInNotification ? `Copied "${String(text)}" to clipboard.` : 'Copied text to clipboard.',
                  };

                  setNotifications((current) => {
                      const next = [notification, ...current];

                      next.slice(4).forEach(({ id }) => {
                          const timeout = timeouts.current.get(id);

                          if (timeout) {
                              clearTimeout(timeout);
                              timeouts.current.delete(id);
                          }
                      });

                      return next.slice(0, 4);
                  });

                  timeouts.current.set(
                      id,
                      setTimeout(() => {
                          setNotifications((current) => current.filter((notification) => notification.id !== id));
                          timeouts.current.delete(id);
                      }, 2500),
                  );

                  children.props.onClick?.(e);
              },
          });

    return (
        <>
            {notifications.length > 0 && (
                <Portal>
                    <div
                        className='fixed z-50 bottom-0 right-0 m-4 pointer-events-none'
                        style={{
                            height: `${64 + (notifications.length - 1) * 12}px`,
                            width: 'min(24rem, calc(100vw - 2rem))',
                        }}
                    >
                        <AnimatePresence initial={false}>
                            {notifications.map((notification, index) => (
                                <motion.div
                                    key={notification.id}
                                    layout
                                    initial={{
                                        opacity: 0,
                                        y: 32,
                                        scale: 0.9,
                                        filter: 'blur(8px)',
                                    }}
                                    animate={{
                                        opacity: Math.max(0.65, 1 - index * 0.12),
                                        y: 0,
                                        scale: Math.max(0.8, 1 - index * 0.05),
                                        filter: 'blur(0px)',
                                    }}
                                    exit={{
                                        opacity: 0,
                                        filter: 'blur(10px)',
                                        scale: 0.95,
                                    }}
                                    transition={{
                                        layout: { type: 'spring', stiffness: 350, damping: 30 },
                                        opacity: { duration: 0.25, ease: 'easeOut' },
                                        filter: { duration: 0.25, ease: 'easeOut' },
                                        scale: { duration: 0.25, ease: 'easeOut' },
                                        y: { type: 'spring', stiffness: 350, damping: 28 },
                                    }}
                                    className='absolute bottom-0 right-0 w-full rounded-ui py-3 px-4 text-gray-200 bg-gray-900 border border-gray-800 shadow-lg pointer-events-auto'
                                    style={{
                                        bottom: `${index * 12}px`,
                                        zIndex: notifications.length - index,
                                        transformOrigin: 'bottom right',
                                    }}
                                >
                                    <p>{notification.text}</p>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </div>
                </Portal>
            )}
            {child}
        </>
    );
};

export default CopyOnClick;
