import { useEffect, useRef, useState } from 'react';
import type { RunRequestState } from './RunScheduleButton';

export default function useScheduleAnimation(processing: boolean, requestState: RunRequestState) {
    const [phase, setPhase] = useState<'idle' | 'spinning' | 'check' | 'skipped'>(processing ? 'spinning' : 'idle');
    const startedAt = useRef<number | null>(null);
    const previousRequest = useRef(requestState);

    useEffect(() => {
        const failed = requestState === 'failed' && previousRequest.current !== 'failed';
        previousRequest.current = requestState;
        if (failed || requestState === 'unknown') {
            startedAt.current = null;
            setPhase('idle');
            return;
        }

        if (processing || requestState === 'pending') {
            startedAt.current ??= Date.now();
            setPhase('spinning');
            return;
        }

        if (requestState === 'skipped') {
            startedAt.current = null;
            setPhase('skipped');
            const timeout = setTimeout(() => setPhase('idle'), 1600);

            return () => clearTimeout(timeout);
        }

        if (startedAt.current === null) return;

        // Even an immediate completion gets one full spin before the completion check.
        let timeout = setTimeout(
            () => {
                startedAt.current = null;
                setPhase('check');
                timeout = setTimeout(() => setPhase('idle'), 1600);
            },
            Math.max(0, 1000 - (Date.now() - startedAt.current)),
        );

        return () => clearTimeout(timeout);
    }, [processing, requestState]);

    return phase;
}
