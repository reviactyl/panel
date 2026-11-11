const modules = import.meta.glob(['../reviactyl/**/*.{ts,tsx}', '!../reviactyl/**/*.{test,spec}.{ts,tsx}'], {
    eager: true,
});

(window as any).__REVIACTYL_MODULES = Object.fromEntries(
    Object.entries(modules).map(([key, value]) => [
        key.replace(/^\.\.\/reviactyl\//, 'reviactyl/').replace(/\.(ts|tsx)$/, ''),
        value,
    ]),
);

export {};
