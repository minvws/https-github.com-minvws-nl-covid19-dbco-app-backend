import { defineConfig } from 'cypress';
import baseConfig from './cypress.config';

export default defineConfig({
    ...baseConfig,
    screenshotOnRunFailure: true,
    e2e: {
        ...baseConfig.e2e,
        baseUrl: 'http://localhost:8080',
        env: {
            ...baseConfig.e2e.env,
            ci: true,
        },
    },
    retries: {
        runMode: 1, // Give flaky tests one more chance to pass (API responses could be too slow in CI runner)
        openMode: 0,
    },
    defaultCommandTimeout: 40000,
});
