import js from '@eslint/js';
import vue from 'eslint-plugin-vue';
import tsPlugin from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';

export default [
    js.configs.recommended,
    ...vue.configs['flat/recommended'],
    {
        files: ['**/*.ts', '**/*.vue'],
        languageOptions: {
            parser: vue.parser,
            parserOptions: {
                parser: tsParser,
                sourceType: 'module',
            },
        },
        plugins: { '@typescript-eslint': tsPlugin },
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
];
