import config from './config';

export default {
    ...config,
    test: {
        ...config.test,
        outputFile: 'report.json',
        reporters: ['json', 'default'],
        coverage: {
            enabled: true,
            reporter: ['text', 'json', 'html'],
            all: true,
            include: [
                'src/**/*.ts',
                'src/**/*.vue',

                '!**/*.d.ts', // no declaration files
                '!**/*.stories.ts', // no storybook files
                '!**/json-forms/stories/*', // no storybook files

                // no specifications and test utils
                '!**/*.spec.ts',
                '!**/__fakes__/*',
                '!**/__mocks__/*',
                '!**/__snapshots__/*',
            ],
            branches: 82.99,
            statements: 81.13,
            functions: 54.16,
        },
    },
};
