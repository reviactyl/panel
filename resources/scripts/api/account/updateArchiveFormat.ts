import http from '@/api/http';

export type ArchiveFormat = 'tar.gz' | 'zip';

export default (format: ArchiveFormat): Promise<void> => http.put('/api/client/account/archive-format', { format });
