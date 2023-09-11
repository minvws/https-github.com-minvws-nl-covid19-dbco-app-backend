const defaultRules = {
    // Eslint default rules
    'no-prototype-builtins': 0,
    'require-await': 1,
    'no-case-declarations': 1,
    'no-useless-escape': 1,
    'no-empty': 1,
    'no-console': ['error', { allow: ['warn', 'error'] }],
    'no-undef': 0, // @see: https://typescript-eslint.io/docs/linting/troubleshooting/#i-get-errors-from-the-no-undef-rule-about-global-variables-not-being-defined-even-though-there-are-no-typescript-errors
    'vuejs-accessibility/aria-unsupported-elements': 0, // causes issues with custom <component is="..."> usage
};

module.exports = {
    extends: ['eslint:recommended', 'plugin:vue/base', 'prettier', 'plugin:vuejs-accessibility/recommended'],
    parser: 'vue-eslint-parser',
    plugins: ['unused-imports', 'import'],
    overrides: [
        {
            extends: [
                'eslint:recommended',
                'plugin:@typescript-eslint/recommended',
                'plugin:vue/base',
                'prettier',
                'plugin:vuejs-accessibility/recommended',
            ],
            files: ['*.ts', '*.vue'],
            parserOptions: {
                parser: '@typescript-eslint/parser',
                sourceType: 'module',
                tsconfigRootDir: __dirname,
                project: [
                    './portal/src/tsconfig.json',
                    './portal/systemtests/tsconfig.json',
                    './shared/packages/**/tsconfig.json',
                ],
                extraFileExtensions: ['.vue'],
            },
            rules: {
                ...defaultRules,
                // Vue
                'vue/html-button-has-type': ['error'],
                'vue/no-v-html': ['error'],

                // Imports
                'unused-imports/no-unused-imports': 'error',
                'import/no-duplicates': 2,
                'import/no-commonjs': 2,

                // Accessibility
                'vuejs-accessibility/no-autofocus': ['error', { ignoreNonDOM: true }],
                'vuejs-accessibility/label-has-for': [
                    'error',
                    {
                        components: ['BLabel'],
                        controlComponents: ['BFormInput'],
                        required: {
                            every: ['id'],
                        },
                        allowChildren: true,
                    },
                ],

                // Typescript
                '@typescript-eslint/no-unused-vars': ['error', { ignoreRestSiblings: true, args: 'none' }],
                '@typescript-eslint/no-floating-promises': 1,
                '@typescript-eslint/no-empty-function': 0,
                '@typescript-eslint/no-explicit-any': 1,
                '@typescript-eslint/no-empty-interface': 1,
                '@typescript-eslint/ban-ts-comment': 1,
                '@typescript-eslint/no-inferrable-types': 1,
                '@typescript-eslint/ban-types': 2,
                '@typescript-eslint/consistent-type-imports': [
                    'error',
                    {
                        disallowTypeAnnotations: false,
                    },
                ],
                'no-warning-comments': [
                    'error',
                    {
                        terms: ['todo', 'fixme'],
                        location: 'anywhere',
                    },
                ],
            },
        },
    ],
    rules: defaultRules,
    env: {
        browser: true,
    },
    root: false,
};
