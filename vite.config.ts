import react from '@vitejs/plugin-react';
import { createHash } from 'node:crypto';
import { readdirSync, readFileSync, statSync, writeFileSync } from 'node:fs';
import { brotliCompressSync, constants as zlibConstants, gzipSync } from 'node:zlib';
import type { Plugin } from 'vite';
import laravel from 'laravel-vite-plugin';
import { dirname, resolve } from 'pathe';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vitest/config';
import tailwindcss from '@tailwindcss/vite';

const localeHash = createHash('sha256');
const hashLocales = (directory: string) => {
    for (const entry of readdirSync(directory, { withFileTypes: true }).sort((a, b) => a.name.localeCompare(b.name))) {
        const path = resolve(directory, entry.name);
        localeHash.update(entry.name);
        if (entry.isDirectory()) hashLocales(path);
        else localeHash.update(readFileSync(path));
    }
};
hashLocales(resolve(dirname(fileURLToPath(import.meta.url)), 'lang'));
const localeVersion = JSON.stringify(localeHash.digest('hex').slice(0, 16));

/**
 * Writes .br and .gz copies of every compressible build file so the web server can send
 * them as-is (nginx `gzip_static`, the `artisan serve` router in server.php) instead of
 * compressing on every request, and at a higher ratio than on-the-fly compression allows.
 */
const precompress = (): Plugin => {
    let outDir = '';
    return {
        name: 'reviactyl:precompress',
        apply: 'build',
        configResolved(config) {
            outDir = resolve(config.root, config.build.outDir);
        },
        writeBundle(_, bundle) {
            for (const fileName of Object.keys(bundle)) {
                if (!/\.(?:js|mjs|css|svg|json|html|txt|ttf|otf)$/.test(fileName)) continue;
                const path = resolve(outDir, fileName);
                if (statSync(path).size < 1024) continue;
                const source = readFileSync(path);
                writeFileSync(`${path}.gz`, gzipSync(source, { level: 9 }));
                writeFileSync(
                    `${path}.br`,
                    brotliCompressSync(source, {
                        params: {
                            [zlibConstants.BROTLI_PARAM_QUALITY]: 11,
                            [zlibConstants.BROTLI_PARAM_SIZE_HINT]: source.length,
                        },
                    }),
                );
            }
        },
    };
};

const plugins = [react(), tailwindcss(), precompress()];

if (process.env.VITEST === undefined) {
    plugins.push(
        laravel({
            input: [
                'resources/scripts/index.tsx',
                'resources/scripts/admin/filePicker.ts',
                'resources/scripts/admin/emailTemplateEditor.ts',
                'resources/css/filament/admin/theme.css',
            ],
        }),
    );
}

export default defineConfig({
    define: {
        __LOCALE_VERSION__: localeVersion,
        ...(process.env.VITEST === undefined
            ? {
                  'process.env': {},
                  'process.platform': null,
                  'process.version': null,
                  'process.versions': null,
                  global: 'globalThis',
              }
            : {}),
    },

    plugins,

    server: {
        watch: {
            ignored: ['**/vendor/**', '**/storage/**', '**/bootstrap/**', '**/lang/**', '**/database/**'],
        },
    },

    build: {
        chunkSizeWarningLimit: 2000,
        rollupOptions: {
            preserveEntrySignatures: 'exports-only',
            treeshake: {
                // Each heroicons module wraps its icon in React.forwardRef() at the top level without
                // a pure annotation, so without this every icon in the set is kept in the bundle.
                moduleSideEffects: (id: string) => !/[\\/]@heroicons[\\/]react[\\/]/.test(id),
            },
        },
    },

    resolve: {
        alias: [
            // heroicons v1 points these entries at CommonJS barrels that require every icon, so all
            // of them end up in the bundle. The package ships an ES module build alongside them.
            ...(['solid', 'outline'] as const).map((style) => ({
                find: new RegExp(`^@heroicons/react/${style}$`),
                replacement: resolve(
                    dirname(fileURLToPath(import.meta.url)),
                    'node_modules',
                    '@heroicons',
                    'react',
                    style,
                    'esm',
                    'index.js',
                ),
            })),
            { find: '@', replacement: resolve(dirname(fileURLToPath(import.meta.url)), 'resources', 'scripts') },
            {
                find: '@definitions',
                replacement: resolve(
                    dirname(fileURLToPath(import.meta.url)),
                    'resources',
                    'scripts',
                    'api',
                    'definitions',
                ),
            },
            {
                find: '@feature',
                replacement: resolve(
                    dirname(fileURLToPath(import.meta.url)),
                    'resources',
                    'scripts',
                    'components',
                    'server',
                    'features',
                ),
            },
        ],
    },

    test: {
        environment: 'happy-dom',
        include: ['resources/scripts/**/*.{spec,test}.{ts,tsx}'],
        globals: true,
    },
});
