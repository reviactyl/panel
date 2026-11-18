import { useLocation } from 'react-router-dom';
import { hashToPath } from '@/helpers';

export const ServerContext = {
    useStoreState: <T>(selector: (state: { server: { data: { id: string } }; files: { directory: string } }) => T) => {
        const { pathname, hash } = useLocation();
        const path = hashToPath(hash);
        const directory = pathname.endsWith('/edit') ? path.slice(0, path.lastIndexOf('/')) : path;
        return selector({ server: { data: { id: 'server' } }, files: { directory } });
    },
};
