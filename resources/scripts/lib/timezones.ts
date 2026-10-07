function getTimezones(): string[] {
    const zones = typeof Intl.supportedValuesOf === 'function' ? Intl.supportedValuesOf('timeZone') : [];

    return zones.includes('UTC') ? zones : ['UTC', ...zones];
}

function getBrowserTimezone(): string | null {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || null;
    } catch {
        return null;
    }
}

function resolveTimezone(timezone: string): string | null {
    if (!/^[A-Za-z]/.test(timezone)) return null;

    try {
        return new Intl.DateTimeFormat('en-US', { timeZone: timezone }).resolvedOptions().timeZone;
    } catch {
        return null;
    }
}

function getTimezoneOffset(timezone: string, date: Date = new Date()): string {
    try {
        const offset = new Intl.DateTimeFormat('en-US', { timeZone: timezone, timeZoneName: 'longOffset' })
            .formatToParts(date)
            .find((part) => part.type === 'timeZoneName')?.value;

        if (!offset) return '';

        return offset === 'GMT' ? 'UTC+00:00' : offset.replace('GMT', 'UTC');
    } catch {
        return '';
    }
}

function formatInTimezone(date: Date, timezone?: string | null): string {
    const options: Intl.DateTimeFormatOptions = {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        timeZoneName: 'short',
    };

    let parts: Intl.DateTimeFormatPart[];
    try {
        parts = new Intl.DateTimeFormat('en-US', { ...options, timeZone: timezone || undefined }).formatToParts(date);
    } catch {
        parts = new Intl.DateTimeFormat('en-US', options).formatToParts(date);
    }

    const get = (type: Intl.DateTimeFormatPartTypes) => parts.find((part) => part.type === type)?.value ?? '';

    const zone = get('timeZoneName').replace(/^GMT(?=[+-])/, 'UTC');

    return `${get('month')} ${get('day')} at ${get('hour')}:${get('minute')} ${get('dayPeriod')} ${zone}`;
}

export { getTimezones, getBrowserTimezone, resolveTimezone, getTimezoneOffset, formatInTimezone };
