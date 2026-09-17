export const taskActionPermissions = [
    'control.console',
    'control.start',
    'control.stop',
    'control.restart',
    'backup.create',
];

export const getTaskActionPermission = (action: string, payload: string): string | null => {
    if (action === 'command') return 'control.console';
    if (action === 'backup') return 'backup.create';

    if (action === 'power') {
        const powerAction = payload.trim();

        if (powerAction === 'start') return 'control.start';
        if (powerAction === 'restart') return 'control.restart';
        if (powerAction === 'stop' || powerAction === 'kill') return 'control.stop';
    }

    return null;
};
