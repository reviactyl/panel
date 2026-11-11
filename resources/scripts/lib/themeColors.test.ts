import { afterEach, describe, expect, it } from 'vitest';
import { contrastRatio, readableColor, updateThemeColors } from './themeColors';

afterEach(() => {
    document.documentElement.removeAttribute('style');
    document.documentElement.className = '';
});

describe('theme contrast', () => {
    it('uses WCAG relative luminance', () => {
        expect(contrastRatio([0, 0, 0], [255, 255, 255])).toBe(21);
        expect(contrastRatio([255, 255, 255], [59, 130, 246])).toBeCloseTo(3.68, 2);
    });

    it.each([
        {
            backgrounds: [
                [212, 212, 216],
                [228, 228, 231],
                [244, 244, 245],
                [250, 250, 250],
            ],
        },
        {
            backgrounds: [
                [63, 63, 70],
                [39, 39, 42],
                [24, 24, 27],
                [9, 9, 11],
            ],
        },
    ] as { backgrounds: [number, number, number][] }[])(
        'keeps custom brand and status text readable across surfaces',
        ({ backgrounds }) => {
            for (const color of [
                [59, 130, 246],
                [61, 143, 31],
                [143, 31, 32],
                [255, 230, 50],
                [160, 80, 220],
                [0, 0, 0],
                [255, 255, 255],
            ] as [number, number, number][]) {
                const result = readableColor(color, backgrounds);
                for (const background of backgrounds)
                    expect(contrastRatio(result, background)).toBeGreaterThanOrEqual(4.5);
            }
        },
    );

    it('keeps a color that already has enough contrast', () => {
        expect(readableColor([30, 40, 50], [[250, 250, 250]])).toEqual([30, 40, 50]);
    });

    it('updates foregrounds after a Designify color change', () => {
        const root = document.documentElement;
        root.style.setProperty('--color-primary', '255 240 0');
        updateThemeColors();
        expect(root.style.getPropertyValue('--foreground-primary')).toBe('0 0 0');
        root.style.setProperty('--color-primary', '30 10 60');
        updateThemeColors();
        expect(root.style.getPropertyValue('--foreground-primary')).toBe('255 255 255');
    });
});
