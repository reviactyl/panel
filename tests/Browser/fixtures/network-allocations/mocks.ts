import type { Allocation } from '@/api/server/getServer';

export const ServerContext = {
    useStoreState: () => 'server-uuid',
    useStoreActions: () => () => undefined,
};
export const usePermissions = () => [true];
const clearFlashes = () => undefined;
const clearAndAddHttpError = () => undefined;
export const useFlashKey = () => ({ clearFlashes, clearAndAddHttpError });
export const useFlash = useFlashKey;
const mutate = async () => [];
export const getServerAllocations = () => ({ mutate });
export const setServerAllocationNotes = async () => undefined;
export const setPrimaryServerAllocation = async (_uuid: string, id: number): Promise<Allocation> => ({
    id,
    ip: '127.0.0.1',
    alias: null,
    port: 65535,
    notes: '',
    isDefault: true,
});
export const deleteServerAllocation = async () => undefined;
