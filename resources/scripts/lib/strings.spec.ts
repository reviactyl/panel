import { capitalize } from '@/lib/strings';
import { describe, it, expect } from 'vitest';

describe('@/lib/strings.ts', function () {
    describe('capitalize()', function () {
        it('should capitalize a string', function () {
            expect(capitalize('foo bar')).toBe('Foo bar');
            expect(capitalize('FOOBAR')).toBe('Foobar');
        });

        it('should handle empty strings', function () {
            expect(capitalize('')).toBe('');
        });
    });
});
