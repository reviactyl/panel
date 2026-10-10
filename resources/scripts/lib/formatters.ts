const BINARY_UNITS = ['Bytes', 'KiB', 'MiB', 'GiB', 'TiB'];
const DECIMAL_UNITS = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

function usesBinaryPrefix(): boolean {
    return window.SiteConfiguration?.useBinaryPrefix !== false;
}

function conversionUnit(): number {
    return usesBinaryPrefix() ? 1024 : 1000;
}

function megabyteLabel(): string {
    return usesBinaryPrefix() ? 'MiB' : 'MB';
}

/**
 * Given a value in megabytes converts it back down into bytes.
 */
function mbToBytes(megabytes: number): number {
    return Math.floor(megabytes * conversionUnit() * conversionUnit());
}

function bytesToMb(bytes: number): number {
    return bytes / conversionUnit() / conversionUnit();
}

/**
 * Given an amount of bytes, converts them into a human readable string format
 * using the configured unit as the divisor.
 */
function bytesToString(bytes: number, decimals = 2): string {
    const k = conversionUnit();
    const units = usesBinaryPrefix() ? BINARY_UNITS : DECIMAL_UNITS;

    if (bytes < 1) return '0 Bytes';

    decimals = Math.floor(Math.max(0, decimals));
    const i = Math.min(Math.floor(Math.log(bytes) / Math.log(k)), units.length - 1);
    const value = Number((bytes / Math.pow(k, i)).toFixed(decimals));

    return `${value} ${units[i]}`;
}

/**
 * Formats an IPv4 or IPv6 address.
 */
function ip(value: string): string {
    if (value.startsWith('[') && value.endsWith(']')) {
        return value;
    }

    return value.includes(':') ? `[${value}]` : value;
}

export { ip, mbToBytes, bytesToMb, megabyteLabel, bytesToString };
