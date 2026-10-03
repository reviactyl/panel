import { motion, useReducedMotion } from 'framer-motion';

const ease = [0.65, 0, 0.35, 1] as const;

export default () => {
    const reduceMotion = useReducedMotion();

    return (
        <svg viewBox='0 0 24 24' className='h-4 w-4 text-success' fill='none' aria-hidden>
            <motion.circle
                cx={12}
                cy={12}
                r={10.5}
                stroke='currentColor'
                strokeWidth={2.5}
                transform='rotate(-90 12 12)'
                initial={{ pathLength: reduceMotion ? 1 : 0 }}
                animate={{ pathLength: 1 }}
                transition={{ duration: 0.4, ease }}
            />
            <motion.path
                d='M7 12.5l3.25 3.25L17 9'
                stroke='currentColor'
                strokeWidth={2.5}
                strokeLinecap='round'
                strokeLinejoin='round'
                initial={{ pathLength: reduceMotion ? 1 : 0 }}
                animate={{ pathLength: 1 }}
                transition={{ duration: 0.3, delay: 0.35, ease }}
            />
        </svg>
    );
};
