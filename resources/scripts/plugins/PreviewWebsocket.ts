import { Websocket } from '@/plugins/Websocket';
import { SocketEvent, SocketRequest } from '@/components/server/events';
import http from '@/api/http';
import { setPreviewPowerState } from '@/api/subuserPreview';
import i18n from '@/i18n';

export class PreviewWebsocket extends Websocket {
    private simulatedStatus: string | null = null;

    constructor(private readonly serverUuid: string) {
        super();
    }

    override emit(event: string | symbol, ...args: any[]): boolean {
        if (event === SocketEvent.STATUS && this.simulatedStatus) {
            args[0] = this.simulatedStatus;
        }

        if (event === SocketEvent.STATS && this.simulatedStatus === 'offline') {
            args[0] = JSON.stringify({
                memory_bytes: 0,
                cpu_absolute: 0,
                disk_bytes: 0,
                network: { rx_bytes: 0, tx_bytes: 0 },
                uptime: 0,
            });
        }

        return super.emit(event, ...args);
    }

    override send(event: string, payload?: string | string[]): void {
        const value = Array.isArray(payload) ? payload[0] : payload;

        if (event === SocketRequest.SET_STATE && value) {
            setPreviewPowerState(this.serverUuid, value)
                .then((status) => {
                    this.simulatedStatus = status;
                    this.emit(SocketEvent.STATUS, status);
                    this.emit(
                        SocketEvent.CONSOLE_OUTPUT,
                        i18n.t('preview.console-status', { ns: 'server/users', status })
                    );
                })
                .catch((error) => this.emit(SocketEvent.DAEMON_ERROR, error.message));
            return;
        }

        if (event === 'send command' && value) {
            http.post(`/api/client/servers/${this.serverUuid}/command`, { command: value })
                .then(() =>
                    this.emit(
                        SocketEvent.CONSOLE_OUTPUT,
                        i18n.t('preview.console-command', { ns: 'server/users', command: value })
                    )
                )
                .catch((error) => this.emit(SocketEvent.DAEMON_ERROR, error.message));
            return;
        }

        super.send(event, payload);
    }
}
