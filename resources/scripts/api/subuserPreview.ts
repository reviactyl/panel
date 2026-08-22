import http from '@/api/http';
import { clearPreviewToken, getPreviewToken, storePreviewToken } from '@/lib/subuserPreviewStorage';

export { clearPreviewToken, getPreviewToken, storePreviewToken };

export interface SubuserPreviewSession {
    uuid: string;
    serverUuid: string;
    serverIdentifier: string;
    serverName: string;
    subuserEmail: string;
    subuserUuid: string;
    permissionCount: number;
    powerStatus: string;
    expiresAt: Date;
}

interface PreviewResponse {
    active: boolean;
    owned_by_tab?: boolean;
    token?: string;
    session?: {
        uuid: string;
        server_uuid: string;
        server_identifier: string;
        server_name: string;
        subuser_email: string;
        subuser_uuid: string;
        permission_count: number;
        power_status: string;
        expires_at: string;
    };
}

export interface SubuserPreviewStatus {
    active: boolean;
    ownedByTab: boolean;
    token?: string;
    session?: SubuserPreviewSession;
}

const transform = (data: PreviewResponse): SubuserPreviewStatus => ({
    active: data.active,
    ownedByTab: data.owned_by_tab === true,
    token: data.token,
    session: data.session
        ? {
              uuid: data.session.uuid,
              serverUuid: data.session.server_uuid,
              serverIdentifier: data.session.server_identifier,
              serverName: data.session.server_name,
              subuserEmail: data.session.subuser_email,
              subuserUuid: data.session.subuser_uuid,
              permissionCount: data.session.permission_count,
              powerStatus: data.session.power_status,
              expiresAt: new Date(data.session.expires_at),
          }
        : undefined,
});

export const getSubuserPreviewStatus = async (): Promise<SubuserPreviewStatus> => {
    const { data } = await http.get<PreviewResponse>('/api/client/subuser-preview');

    return transform(data);
};

export const startSubuserPreview = async (
    serverUuid: string,
    subuserUuid: string,
    replace = false
): Promise<SubuserPreviewStatus> => {
    const { data } = await http.post<PreviewResponse>(
        `/api/client/servers/${serverUuid}/users/${subuserUuid}/preview`,
        { replace }
    );

    return transform(data);
};

export const heartbeatSubuserPreview = async (): Promise<SubuserPreviewStatus> => {
    const { data } = await http.post<PreviewResponse>('/api/client/subuser-preview/heartbeat');

    return transform(data);
};

export const stopSubuserPreview = async (): Promise<void> => {
    await http.delete('/api/client/subuser-preview');
};

export const setPreviewPowerState = async (serverUuid: string, signal: string): Promise<string> => {
    const { data } = await http.post<{ status: string }>(`/api/client/servers/${serverUuid}/power`, { signal });

    return data.status;
};
