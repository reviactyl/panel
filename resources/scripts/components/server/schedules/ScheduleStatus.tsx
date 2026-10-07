import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import Spinner from '@/reviactyl/elements/Spinner';
import BackupCompletedCheck from '@/components/server/backups/BackupCompletedCheck';
import type { RunRequestState } from './RunScheduleButton';
import useScheduleAnimation from './useScheduleAnimation';

export default function ScheduleStatus({
    active,
    processing,
    requestState,
}: {
    active: boolean;
    processing: boolean;
    requestState: RunRequestState;
}) {
    const phase = useScheduleAnimation(processing, requestState);
    const reduceMotion = useReducedMotion();

    return (
        <span className='ml-4 flex shrink-0 items-center' role='status'>
            <AnimatePresence mode='wait' initial={false}>
                <motion.span
                    key={phase}
                    className='flex items-center'
                    initial={reduceMotion ? false : { opacity: 0, scale: 0.8 }}
                    animate={{ opacity: 1, scale: 1 }}
                    exit={reduceMotion ? { opacity: 1, scale: 1 } : { opacity: 0, scale: 0.8 }}
                    transition={{ duration: reduceMotion ? 0 : 0.15 }}
                >
                    {phase === 'spinning' ? (
                        <span className='flex items-center rounded-full bg-gray-700 px-2 py-px text-xs uppercase text-white'>
                            <span className='mr-2 flex h-3.5 w-3.5 shrink-0 items-center justify-center' aria-hidden>
                                <Spinner size='small' className='!h-3.5 !w-3.5 motion-reduce:animate-none' />
                            </span>
                            Processing
                        </span>
                    ) : phase === 'check' ? (
                        <BackupCompletedCheck />
                    ) : (
                        <span
                            className={`rounded-ui px-2 py-px text-xs uppercase ${
                                active ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger'
                            }`}
                        >
                            {active ? 'Active' : 'Inactive'}
                        </span>
                    )}
                </motion.span>
            </AnimatePresence>
        </span>
    );
}
