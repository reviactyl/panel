import useSWR, { SWRResponse } from 'swr';
import http, { FractalResponseList } from '@/api/http';
import { AxiosError } from 'axios';
import { useUserSWRKey } from '@/plugins/useSWRKey';

export type AlertPlacement = 'dashboard' | 'server' | 'account' | 'auth';

export interface AlertButton {
    label: string;
    url: string;
    style: 'primary' | 'secondary';
    newTab: boolean;
}

export interface PanelAlert {
    uuid: string;
    type: string;
    title: string | null;
    message: string;
    color: string | null;
    buttons: AlertButton[];
    dismissible: boolean;
    dismissOnAction: boolean;
    redisplayAfterDays: number | null;
    dismissalsResetAt: Date | null;
}

const rawDataToAlert = (data: any): PanelAlert => ({
    uuid: data.uuid,
    type: data.type,
    title: data.title ?? null,
    message: data.message,
    color: data.color ?? null,
    buttons: (data.buttons ?? []).map((button: any) => ({
        label: button.label,
        url: button.url,
        style: button.style === 'secondary' ? 'secondary' : 'primary',
        newTab: !!button.new_tab,
    })),
    dismissible: !!data.dismissible,
    dismissOnAction: !!data.dismiss_on_action,
    redisplayAfterDays: data.redisplay_after_days ?? null,
    dismissalsResetAt: data.dismissals_reset_at ? new Date(data.dismissals_reset_at) : null,
});

const getAlerts = async (placement: AlertPlacement, server?: string): Promise<PanelAlert[]> => {
    const { data } =
        placement === 'auth'
            ? await http.get('/auth/alerts')
            : await http.get('/api/client/alerts', { params: { placement, server } });

    return (data as FractalResponseList).data.map((datum) => rawDataToAlert(datum.attributes));
};

const useAlerts = (placement: AlertPlacement, server?: string): SWRResponse<PanelAlert[], AxiosError> => {
    const key = useUserSWRKey(['alerts', placement, server ?? null]);
    const ready = placement !== 'server' || !!server;

    return useSWR(ready ? key : null, () => getAlerts(placement, server), {
        refreshInterval: 5 * 60 * 1000,
        shouldRetryOnError: false,
    });
};

const dismissAlert = async (uuid: string, placement: AlertPlacement, server?: string): Promise<void> => {
    await http.post(`/api/client/alerts/${uuid}/dismiss`, { placement, server });
};

const trackAlertClick = async (uuid: string, placement: AlertPlacement, server?: string): Promise<void> => {
    await http.post(
        `/api/client/alerts/${uuid}/click`,
        { placement, server },
        { adapter: 'fetch', fetchOptions: { keepalive: true } },
    );
};

export { useAlerts, getAlerts, dismissAlert, trackAlertClick };
