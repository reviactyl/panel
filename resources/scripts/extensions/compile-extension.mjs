//
// Rextension Compiler.
// (c) 2026 Reviactyl Software and Contributors.
// Licensed under: MIT License.
// @https://github.com/reviactyl/panel
//

import fs from 'node:fs/promises';
import fsSync from 'node:fs';
import path from 'node:path';
import ts from 'typescript';

const [, , extensionRootArg, modeArg] = process.argv;

if (!extensionRootArg) {
    console.error(`\x1b[34m[extensions:watch]\x1b[0m Missing extension root argument.`);
    process.exit(1);
}

const extensionRoot = path.resolve(extensionRootArg);
const sourceRoot = path.join(extensionRoot, 'frontend', 'src');
const distRoot = path.join(extensionRoot, 'frontend', 'dist');
const panelScriptsRoot = path.resolve(process.cwd(), 'resources', 'scripts');
const panelScriptsDevOrigin = (
    process.env.EXTENSIONS_DEV_ORIGIN ||
    process.env.VITE_DEV_SERVER_ORIGIN ||
    'https://reviactyl.test'
).replace(/\/+$/, '');
const watchMode = modeArg !== '--once';

const REACT_SHIM = [
    'const React = window.React;',
    "if (!React) throw new Error('window.React is not available for extension module rendering.');",
    '',
].join('\n');

const PANEL_ALIAS_RUNTIME_PREFIX = '/resources/scripts/';

const isTsSource = (filePath) => /\.(ts|tsx)$/i.test(filePath) && !/\.d\.ts$/i.test(filePath);

const toPosix = (value) => value.replaceAll(path.sep, '/');

const relativeFromSource = (filePath) => path.relative(sourceRoot, filePath);

const outputPathFor = (filePath) => {
    const relative = relativeFromSource(filePath).replace(/\.(tsx|ts)$/i, '.js');

    return path.join(distRoot, relative);
};

const resolvePanelAliasRuntimePath = (aliasedPath) => {
    const normalized = aliasedPath.replace(/^\/+/, '');
    const absoluteBase = path.join(panelScriptsRoot, normalized);

    const candidates = [
        absoluteBase,
        `${absoluteBase}.ts`,
        `${absoluteBase}.tsx`,
        `${absoluteBase}.js`,
        `${absoluteBase}.jsx`,
        path.join(absoluteBase, 'index.ts'),
        path.join(absoluteBase, 'index.tsx'),
        path.join(absoluteBase, 'index.js'),
        path.join(absoluteBase, 'index.jsx'),
    ];

    const found = candidates.find((candidate) => fsSync.existsSync(candidate));
    if (!found) {
        return `${panelScriptsDevOrigin}${PANEL_ALIAS_RUNTIME_PREFIX}${normalized}`;
    }

    const relativeFromScripts = path.relative(panelScriptsRoot, found);
    return `${panelScriptsDevOrigin}${PANEL_ALIAS_RUNTIME_PREFIX}${toPosix(relativeFromScripts)}`;
};

const rewritePanelAliases = (code) =>
    code.replace(/(["'])@\/([^"']+)\1/g, (_match, quote, importPath) => {
        const runtimePath = resolvePanelAliasRuntimePath(importPath);
        return `${quote}${runtimePath}${quote}`;
    });

const parseImportSpecifiers = (clause) => {
    const trimmed = clause.trim();

    if (trimmed.startsWith('* as ')) {
        return { namespace: trimmed.replace(/^\*\s+as\s+/, '').trim(), defaultName: null, named: [] };
    }

    const commaIndex = trimmed.indexOf(',');
    if (commaIndex >= 0) {
        const defaultName = trimmed.slice(0, commaIndex).trim();
        const rest = trimmed.slice(commaIndex + 1).trim();

        if (rest.startsWith('{') && rest.endsWith('}')) {
            const named = rest
                .slice(1, -1)
                .split(',')
                .map((item) => item.trim())
                .filter(Boolean)
                .map((item) => {
                    const match = item.match(/^(\w+)\s+as\s+(\w+)$/);
                    return match ? { imported: match[1], local: match[2] } : { imported: item, local: item };
                });

            return { namespace: null, defaultName, named };
        }

        return { namespace: null, defaultName, named: [] };
    }

    if (trimmed.startsWith('{') && trimmed.endsWith('}')) {
        const named = trimmed
            .slice(1, -1)
            .split(',')
            .map((item) => item.trim())
            .filter(Boolean)
            .map((item) => {
                const match = item.match(/^(\w+)\s+as\s+(\w+)$/);
                return match ? { imported: match[1], local: match[2] } : { imported: item, local: item };
            });

        return { namespace: null, defaultName: null, named };
    }

    return { namespace: null, defaultName: trimmed, named: [] };
};

const rewriteReviactylImportsToGlobals = (code) => {
    let index = 0;

    return code.replace(
        /^import\s+(.+?)\s+from\s+['"]@\/reviactyl\/([^'"]+)['"];?\s*$/gm,
        (_match, clauseRaw, subPathRaw) => {
            const subPath = String(subPathRaw).replace(/^\/+/, '').replace(/\.(ts|tsx|js|jsx)$/i, '');
            const moduleKey = `reviactyl/${subPath}`;
            const moduleVar = `__extReviactylMod${index++}`;
            const clause = parseImportSpecifiers(String(clauseRaw));

            const statements = [
                `const ${moduleVar} = window.__REVIACTYL_MODULES?.[${JSON.stringify(moduleKey)}];`,
                `if (!${moduleVar}) throw new Error(${JSON.stringify(`Missing Reviactyl runtime module: ${moduleKey}`)});`,
            ];

            if (clause.namespace) {
                statements.push(`const ${clause.namespace} = ${moduleVar};`);
            }

            if (clause.defaultName) {
                statements.push(`const ${clause.defaultName} = ${moduleVar}.default ?? ${moduleVar};`);
            }

            for (const named of clause.named) {
                statements.push(`const ${named.local} = ${moduleVar}[${JSON.stringify(named.imported)}];`);
            }

            return statements.join('\n');
        }
    );
};

const ensureDir = async (dirPath) => {
    await fs.mkdir(dirPath, { recursive: true });
};

const listFilesRecursive = async (dirPath) => {
    let entries;

    try {
        entries = await fs.readdir(dirPath, { withFileTypes: true });
    } catch {
        return [];
    }

    const files = await Promise.all(
        entries.map(async (entry) => {
            const resolved = path.join(dirPath, entry.name);

            if (entry.isDirectory()) {
                return listFilesRecursive(resolved);
            }

            return [resolved];
        })
    );

    return files.flat();
};

const compileFile = async (filePath) => {
    const sourceText = await fs.readFile(filePath, 'utf8');
    const isTsx = /\.tsx$/i.test(filePath);

    const result = ts.transpileModule(sourceText, {
        fileName: filePath,
        reportDiagnostics: true,
        compilerOptions: {
            target: ts.ScriptTarget.ES2020,
            module: ts.ModuleKind.ES2020,
            jsx: isTsx ? ts.JsxEmit.React : ts.JsxEmit.Preserve,
            esModuleInterop: true,
            sourceMap: false,
            removeComments: false,
        },
    });

    if (Array.isArray(result.diagnostics) && result.diagnostics.length > 0) {
        for (const diag of result.diagnostics) {
            const message = ts.flattenDiagnosticMessageText(diag.messageText, '\n');
            if (diag.file && typeof diag.start === 'number') {
                const { line, character } = diag.file.getLineAndCharacterOfPosition(diag.start);
                console.error(`[extensions:watch] ${toPosix(diag.file.fileName)}:${line + 1}:${character + 1} ${message}`);
            } else {
                console.error(`\x1b[34m[extensions:watch]\x1b[0m ${message}`);
            }
        }
    }

    let output = result.outputText;

    if (isTsx) {
        output = output.replace(/^import\s+React\s+from\s+['"]react['"];?\s*$/m, '');
        output = output.replace(/^import\s+\*\s+as\s+React\s+from\s+['"]react['"];?\s*$/m, '');

        if (!output.includes('const React = window.React;')) {
            output = `${REACT_SHIM}${output}`;
        }
    }

    output = rewriteReviactylImportsToGlobals(output);
    output = rewritePanelAliases(output);

    const outPath = outputPathFor(filePath);
    await ensureDir(path.dirname(outPath));
    await fs.writeFile(outPath, output, 'utf8');

    console.log(`\x1b[34m[extensions:watch]\x1b[0m compiled ${toPosix(relativeFromSource(filePath))} -> ${toPosix(path.relative(extensionRoot, outPath))}`);
};

const removeOutputFor = async (filePath) => {
    const outPath = outputPathFor(filePath);

    try {
        await fs.unlink(outPath);
        console.log(`\x1b[34m[extensions:watch]\x1b[0m removed ${toPosix(path.relative(extensionRoot, outPath))}`);
    } catch {
        //
    }
};

const snapshot = async () => {
    const files = (await listFilesRecursive(sourceRoot)).filter(isTsSource);
    const state = new Map();

    for (const filePath of files) {
        const stat = await fs.stat(filePath);
        state.set(filePath, stat.mtimeMs);
    }

    return state;
};

const compileAll = async () => {
    const files = (await listFilesRecursive(sourceRoot)).filter(isTsSource);

    for (const filePath of files) {
        await compileFile(filePath);
    }
};

const run = async () => {
    await ensureDir(distRoot);

    await compileAll();

    if (!watchMode) {
        return;
    }

    console.log(`\x1b[34m[extensions:watch]\x1b[0m watching ${toPosix(path.relative(process.cwd(), sourceRoot))}`);

    let known = await snapshot();
    let running = false;

    setInterval(async () => {
        if (running) {
            return;
        }

        running = true;

        try {
            const current = await snapshot();

            for (const [filePath, mtime] of current) {
                const previous = known.get(filePath);
                if (previous === undefined || previous !== mtime) {
                    await compileFile(filePath);
                }
            }

            for (const [filePath] of known) {
                if (!current.has(filePath)) {
                    await removeOutputFor(filePath);
                }
            }

            known = current;
        } catch (error) {
            console.error(`\x1b[34m[extensions:watch]\x1b[0m watch loop error`, error);
        } finally {
            running = false;
        }
    }, 700);
};

run().catch((error) => {
    console.error(`\x1b[34m[extensions:watch]\x1b[0m fatal`, error);
    process.exit(1);
});
