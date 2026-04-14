import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';

interface Props {
    timeout?: number;
    in?: boolean;
    appear?: boolean;
    unmountOnExit?: boolean;
    children: React.ReactNode;
}

const Fade = React.forwardRef<HTMLDivElement, Props>(
    ({ timeout = 150, in: isIn = true, appear = false, unmountOnExit = false, children }, ref) => {
        const duration = timeout / 1000;

        // If unmountOnExit is true, use AnimatePresence
        if (unmountOnExit) {
            return (
                <AnimatePresence>
                    {isIn && (
                        <motion.div
                            ref={ref}
                            initial={{ opacity: appear ? 0 : 1 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            transition={{ duration, ease: 'easeIn' }}
                            style={{ width: '100%', height: '100%' }}
                        >
                            {children}
                        </motion.div>
                    )}
                </AnimatePresence>
            );
        }

        return (
            <motion.div
                ref={ref}
                initial={{ opacity: appear ? 0 : 1 }}
                animate={{ opacity: isIn ? 1 : 0 }}
                transition={{ duration, ease: 'easeIn' }}
                style={{ width: '100%', height: '100%' }}
            >
                {children}
            </motion.div>
        );
    }
);
Fade.displayName = 'Fade';

export default Fade;
