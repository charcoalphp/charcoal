'use strict';

const globals = require('globals');

module.exports = [
    {
        files: [ 'assets/src/scripts/**/*.js' ],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'script',
            globals: {
                ...globals.browser,
                ...globals.amd,
                ...globals.es2015,
                $: 'readonly',
                BootstrapDialog: 'readonly',
                Charcoal: 'readonly',
                console: 'readonly',
                jQuery: 'readonly',
                module: 'readonly',
                require: 'readonly'
            }
        },
        rules: {
            curly: [ 2, 'all' ],
            eqeqeq: [ 'error', 'always', { null: 'ignore' } ],
            'no-undef': 2,
            'no-unused-vars': 2,
            'no-empty': [ 2, { allowEmptyCatch: true } ],
            'no-implicit-coercion': [ 2, { boolean: true, string: false, number: true } ],
            'no-with': 2,
            'brace-style': [ 2, '1tbs', { allowSingleLine: true } ],
            'no-mixed-spaces-and-tabs': 2,
            'no-multiple-empty-lines': 2,
            'no-multi-str': 2,
            'keyword-spacing': [ 2, {} ],
            'key-spacing': [ 2, { beforeColon: false, afterColon: true, mode: 'minimum' } ],
            'space-unary-ops': [ 2, { words: false, nonwords: false } ],
            'space-before-function-paren': [ 2, { anonymous: 'always', named: 'never', asyncArrow: 'always' } ],
            'array-bracket-spacing': [ 2, 'always' ],
            'space-in-parens': [ 2, 'never' ],
            yoda: [ 2, 'never' ],
            'max-len': [ 2, 160 ],
            'dot-notation': 2,
            'eol-last': 2,
            'operator-linebreak': [ 2, 'after', { overrides: { '?': 'before', ':': 'before' } } ],
            'wrap-iife': 2,
            'space-infix-ops': 2,
            'consistent-this': [ 2, 'that' ],
            indent: [ 2, 4, { SwitchCase: 1 } ],
            'linebreak-style': [ 2, 'unix' ],
            quotes: [ 2, 'single' ]
        }
    }
];
