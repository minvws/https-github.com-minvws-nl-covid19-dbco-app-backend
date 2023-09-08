import { defineConfig } from 'cypress';

export default defineConfig({
    e2e: {
        setupNodeEvents(on, config) {
            // This ESlint rule is there for Vite, but cypress uses webpack
            // eslint-disable-next-line @typescript-eslint/no-var-requires
            require('@cypress/grep/src/plugin')(config);
            return config;
        },
        baseUrl: 'http://localhost:8084',
        specPattern: 'tests/**/*.test.ts',
        viewportWidth: 1400,
        viewportHeight: 1000,
        supportFile: 'support.ts',
        env: {
            grepOmitFiltered: true,
            grepFilterSpecs: true,
        },
    },
    screenshotsFolder: 'artifacts/screenshots',
    screenshotOnRunFailure: false,
    videosFolder: 'artifacts/videos',
    video: false,
    downloadsFolder: 'artifacts/downloads',
    defaultCommandTimeout: 8000,
    scrollBehavior: 'center',
});
