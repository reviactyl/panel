import { createContext } from 'react';
import type { ServerStats } from '@/api/server/getServerResourceUsage';

export interface ResourceEntry {
    stats: ServerStats | null;
    lastUpdated?: number;
    pending?: Promise<ServerStats>;
}

export const ServerResourcesContext = createContext<Map<string, ResourceEntry> | null>(null);
