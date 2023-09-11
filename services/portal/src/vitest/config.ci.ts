import { mergeConfig } from 'vite';
import config from './config';

export default mergeConfig(config, {
    test: {
        outputFile: 'report.json',
        reporters: ['json', 'default'],
        coverage: {
            enabled: true,
            reporter: ['text', 'json', 'html'],
            all: true,
            include: [
                'resources/**/*.ts',
                'resources/**/*.vue',

                // no declaration files
                '!**/*.d.ts',

                // no api data transfer objects
                '!resources/js/api/*.dto.ts',

                // no generated files
                '!resources/js/types/enums/*',

                // no specifications and test utils
                '!**/*.spec.ts',
                '!**/__fakes__/*',
                '!**/__mocks__/*',
                '!**/__snapshots__/*',
                '!resources/js/integration-specs/*',

                // no config files
                '!global-test-setup.ts',
                '!vitest.config.ci.ts',
                '!vitest.setup.ts',
            ],
            branches: 88.3,
            statements: 71.9, // line coverage is equal to statements given project formatting rules
            functions: 67.9,
        },
    },
});
