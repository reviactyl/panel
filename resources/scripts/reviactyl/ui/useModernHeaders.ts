import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';

export default function useModernHeaders() {
    return useStoreState((state: ApplicationStore) => state.designify.data?.headerStyle === 'modern');
}
