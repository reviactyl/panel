import { formatInTimezone, getTimezoneOffset, getTimezones, resolveTimezone } from '@/lib/timezones';
import { describe, it, expect } from 'vitest';

describe('@/lib/timezones.ts', function () {
    describe('getTimezones()', function () {
        it('should always include UTC', function () {
            expect(getTimezones()).toContain('UTC');
        });
    });

    describe('resolveTimezone()', function () {
        it('should return the canonical name of a known timezone', function () {
            expect(resolveTimezone('europe/berlin')).toBe('Europe/Berlin');
        });

        it('should return null for anything that is not a named timezone', function () {
            expect(resolveTimezone('Not/AZone')).toBeNull();
            expect(resolveTimezone('+01:00')).toBeNull();
            expect(resolveTimezone('')).toBeNull();
        });
    });

    describe('getTimezoneOffset()', function () {
        it('should return the offset in effect on the given date', function () {
            expect(getTimezoneOffset('America/New_York', new Date('2026-01-15T12:00:00Z'))).toBe('UTC-05:00');
            expect(getTimezoneOffset('America/New_York', new Date('2026-07-15T12:00:00Z'))).toBe('UTC-04:00');
            expect(getTimezoneOffset('Asia/Kolkata', new Date('2026-01-15T12:00:00Z'))).toBe('UTC+05:30');
            expect(getTimezoneOffset('UTC', new Date('2026-01-15T12:00:00Z'))).toBe('UTC+00:00');
        });

        it('should return an empty string for an unknown timezone', function () {
            expect(getTimezoneOffset('Not/AZone')).toBe('');
        });
    });

    describe('formatInTimezone()', function () {
        it('should format the date as it reads in the given timezone', function () {
            const date = new Date('2026-10-05T07:00:00Z');

            expect(formatInTimezone(date, 'America/New_York')).toBe('Oct 5 at 3:00 AM EDT');
            expect(formatInTimezone(date, 'UTC')).toBe('Oct 5 at 7:00 AM UTC');
            expect(formatInTimezone(date, 'Asia/Tokyo')).toBe('Oct 5 at 4:00 PM UTC+9');
        });

        it('should not throw for an unknown timezone', function () {
            expect(formatInTimezone(new Date('2026-10-05T07:00:00Z'), 'Not/AZone')).toContain('Oct');
        });
    });
});
