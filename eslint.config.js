import js from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier';

export default [
    {
        ignores: [
            'vendor/**',
            'node_modules/**',
            'public/build/**',
            'public/hot/**',
            'storage/**',
            'bootstrap/cache/**',
            '.agents/**',
        ],
    },
    js.configs.recommended,
    {
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                // Browser globals
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                navigator: 'readonly',
                setTimeout: 'readonly',
                clearTimeout: 'readonly',
                setInterval: 'readonly',
                clearInterval: 'readonly',
                fetch: 'readonly',
                localStorage: 'readonly',
                sessionStorage: 'readonly',
                CustomEvent: 'readonly',

                // Project globals
                Alpine: 'readonly',
                axios: 'readonly',
            },
        },
        rules: {
            'no-unused-vars': 'warn',
            'no-console': 'off',
        },
    },
    eslintConfigPrettier,
];
