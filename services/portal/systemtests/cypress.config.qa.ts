import { defineConfig } from 'cypress';
import baseConfig from './cypress.ci.config';

export default defineConfig({
    ...baseConfig,
    e2e: {
        ...baseConfig.e2e,
        baseUrl: 'https://www-dev.bco-portaal.nl',
    },
});
