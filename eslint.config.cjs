const js = require('@eslint/js');
const {fixupPluginRules} = require('@eslint/compat');
const globals = require('globals');
const tsParser = require('@typescript-eslint/parser');
const tsPlugin = require('@typescript-eslint/eslint-plugin');
const reactPlugin = require('eslint-plugin-react');
const reactHooksPlugin = require('eslint-plugin-react-hooks');
const eslintConfigPrettier = require('eslint-config-prettier');
const prettierPlugin = require('eslint-plugin-prettier');

const reactPluginCompat = fixupPluginRules(reactPlugin);
const reactHooksPluginCompat = fixupPluginRules(reactHooksPlugin);

module.exports = [
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'public/**',
            'resources/views/**',
            'resources/lang/**',
            'vite.config.ts',
        ],
    },
    js.configs.recommended,
    ...tsPlugin.configs['flat/recommended'],
    {
        files: ['resources/scripts/**/*.{ts,tsx}'],
        languageOptions: {
            parser: tsParser,
            ecmaVersion: 'latest',
            sourceType: 'module',
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
                project: './tsconfig.json',
                tsconfigRootDir: __dirname,
            },
            globals: {
                ...globals.browser,
                ...globals.es2022,
            },
        },
        plugins: {
            '@typescript-eslint': tsPlugin,
            react: reactPluginCompat,
            'react-hooks': reactHooksPluginCompat,
            prettier: prettierPlugin,
        },
        settings: {
            react: {
                pragma: 'React',
                version: 'detect',
            },
            linkComponents: [
                {name: 'Link', linkAttribute: 'to'},
                {name: 'NavLink', linkAttribute: 'to'},
            ],
        },
        rules: {
            ...reactPlugin.configs.flat.recommended.rules,
            ...reactPlugin.configs.flat['jsx-runtime'].rules,
            'prettier/prettier': ['error', {}, { usePrettierrc: true }],
            eqeqeq: 'error',
            'no-constant-binary-expression': 'off',
            'no-useless-assignment': 'off',
            // TypeScript can infer this significantly better than eslint ever can.
            'react/prop-types': 0,
            'react/display-name': 0,
            'react/no-unknown-property': ['error', {ignore: ['css']}],
            '@typescript-eslint/no-explicit-any': 0,
            '@typescript-eslint/no-non-null-assertion': 0,
            // This setup is required to avoid a spam of errors when running eslint about React being
            // used before it is defined.
            //
            // @see https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-use-before-define.md#how-to-use
            'no-use-before-define': 0,
            '@typescript-eslint/no-use-before-define': 'warn',
            '@typescript-eslint/no-unused-vars': ['warn', {argsIgnorePattern: '^_', varsIgnorePattern: '^_'}],
            '@typescript-eslint/ban-ts-comment': ['error', {'ts-expect-error': 'allow-with-description'}],
        },
    },
    eslintConfigPrettier,
];
