const _CONVERSION_UNIT = 1024;

/**
 * Given a value in megabytes converts it back down into bytes.
 */
function mbToBytes(megabytes: number): number {
    return Math.floor(megabytes * _CONVERSION_UNIT * _CONVERSION_UNIT);
}

/**
 * Given an amount of bytes, converts them into a human readable string format
 * using "1024" as the divisor.
 */
function bytesToString(bytes: number, decimals = 2): string {
    const k = _CONVERSION_UNIT;

    if (bytes < 1) return '0 Bytes';

    decimals = Math.floor(Math.max(0, decimals));
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const value = Number((bytes / Math.pow(k, i)).toFixed(decimals));

    return `${value} ${['Bytes', 'KiB', 'MiB', 'GiB', 'TiB'][i]}`;
}

/**
 * Formats an IPv4 or IPv6 address.
 */
function ip(value: string): string {
    // Efficient IPv6 regex avoids ambiguous backtracking
    return /^(([a-f0-9]{1,4}:){7}[a-f0-9]{1,4}|(([a-f0-9]{1,4}:){1,7}:)|(([a-f0-9]{1,4}:){1,6}:[a-f0-9]{1,4})|(([a-f0-9]{1,4}:){1,5}(:[a-f0-9]{1,4}){1,2})|(([a-f0-9]{1,4}:){1,4}(:[a-f0-9]{1,4}){1,3})|(([a-f0-9]{1,4}:){1,3}(:[a-f0-9]{1,4}){1,4})|(([a-f0-9]{1,4}:){1,2}(:[a-f0-9]{1,4}){1,5})|([a-f0-9]{1,4}:)((:[a-f0-9]{1,4}){1,6})|(:((:[a-f0-9]{1,4}){1,7}|:)))(%[0-9a-zA-Z]{1,})?$/.test(value) ? `[${value}]` : value;
}

export { ip, mbToBytes, bytesToString };
