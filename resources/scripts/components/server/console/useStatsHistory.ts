import { useRef, type RefObject } from 'react';
import { SocketEvent } from '@/components/server/events';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';

export const STATS_HISTORY_LENGTH = 20;

export default function useStatsHistory(): RefObject<string[]> {
    const frames = useRef<string[]>([]);

    useWebsocketEvent(SocketEvent.STATS, (data: string) => {
        frames.current.push(data);
        if (frames.current.length > STATS_HISTORY_LENGTH) frames.current.shift();
    });

    return frames;
}
