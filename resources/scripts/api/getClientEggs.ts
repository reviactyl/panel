import http from '@/api/http';

export interface ClientEgg {
    id: number;
    name: string;
}

export default (): Promise<ClientEgg[]> => {
    return http.get('/api/client/eggs').then(({ data }) => data.data || []);
};
