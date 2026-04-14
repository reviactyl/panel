import React, { useEffect, useRef, useState } from 'react';
import styled from 'styled-components';
import { useStoreActions, useStoreState } from 'easy-peasy';
import { randomInt } from '@/helpers';
import { motion, AnimatePresence } from 'framer-motion';
import tw from 'twin.macro';

const BarFill = styled.div`
    ${tw`h-full bg-cyan-400`};
    transition: 250ms ease-in-out;
    box-shadow: 0 -2px 10px 2px hsl(178, 78%, 57%);
`;

type Timer = ReturnType<typeof setTimeout>;

export default () => {
    const interval = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    const timeout = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    const [visible, setVisible] = useState(false);
    const progress = useStoreState((state) => state.progress.progress);
    const continuous = useStoreState((state) => state.progress.continuous);
    const setProgress = useStoreActions((actions) => actions.progress.setProgress);

    useEffect(() => {
        return () => {
            void (timeout.current && clearTimeout(timeout.current));
            void (interval.current && clearInterval(interval.current));
        };
    }, []);

    useEffect(() => {
        setVisible((progress || 0) > 0);

        if (progress === 100) {
            timeout.current = setTimeout(() => setProgress(undefined), 500);
        }
    }, [progress]);

    useEffect(() => {
        if (!continuous) {
            void (interval.current && clearInterval(interval.current));
            return;
        }

        if (!progress || progress === 0) {
            setProgress(randomInt(20, 30));
        }
    }, [continuous]);

    useEffect(() => {
        if (continuous) {
            void (interval.current && clearInterval(interval.current));
            if ((progress || 0) >= 90) {
                setProgress(90);
            } else {
                interval.current = setTimeout(() => setProgress((progress || 0) + randomInt(1, 5)), 500);
            }
        }
    }, [progress, continuous]);

    return (
        <div css={tw`w-full fixed`} style={{ height: '2px' }}>
            <AnimatePresence>
                {visible && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.15, ease: 'easeIn' }}
                    >
                        <BarFill style={{ width: progress === undefined ? '100%' : `${progress}%` }} />
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
};
