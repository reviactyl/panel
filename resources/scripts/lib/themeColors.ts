type Color = [number, number, number];

const luminance = (color: Color) =>
    color
        .map((channel) => channel / 255)
        .map((channel) => (channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4))
        .reduce((value, channel, index) => value + channel * [0.2126, 0.7152, 0.0722][index]!, 0);

export const contrastRatio = (foreground: Color, background: Color) => {
    const values = [luminance(foreground), luminance(background)];

    return (Math.max(...values) + 0.05) / (Math.min(...values) + 0.05);
};

export const readableColor = (color: Color, backgrounds: Color[], minimum = 4.5): Color => {
    const contrast = (candidate: Color) => Math.min(...backgrounds.map((bg) => contrastRatio(candidate, bg)));
    if (contrast(color) >= minimum) return color;

    const black: Color = [0, 0, 0];
    const white: Color = [255, 255, 255];
    const target = contrast(black) > contrast(white) ? black : white;

    for (let step = 1; step <= 100; step++) {
        const candidate = color.map((channel, index) =>
            Math.round(channel + (target[index]! - channel) * (step / 100)),
        ) as Color;
        if (contrast(candidate) >= minimum) return candidate;
    }

    return target;
};

export const updateThemeColors = () => {
    const root = document.documentElement;
    const styles = getComputedStyle(root);
    const read = (name: string, fallback: Color): Color => {
        const channels = styles.getPropertyValue(name).trim().split(/\s+/).map(Number);

        return channels.length === 3 && channels.every((value) => Number.isFinite(value) && value >= 0 && value <= 255)
            ? (channels as Color)
            : fallback;
    };
    const dark = root.classList.contains('dark');
    const backgrounds = ['700', '800', '900', '950'].map((step) =>
        read(`--color-${step}`, dark ? [24, 24, 27] : [244, 244, 245]),
    );
    const defaults: Record<string, Color> = {
        primary: [59, 130, 246],
        secondary: [43, 43, 64],
        success: [61, 143, 31],
        danger: [143, 31, 32],
    };

    for (const [name, fallback] of Object.entries(defaults)) {
        const color = read(`--color-${name}`, fallback);
        const foreground: Color =
            contrastRatio([255, 255, 255], color) >= contrastRatio([0, 0, 0], color) ? [255, 255, 255] : [0, 0, 0];
        root.style.setProperty(`--foreground-${name}`, foreground.join(' '));
        root.style.setProperty(`--text-${name}`, readableColor(color, backgrounds).join(' '));
    }

    root.style.setProperty('--text-muted', readableColor(read('--color-400', [113, 113, 123]), backgrounds).join(' '));
};
