import { defineConfig } from 'orval';

export default defineConfig({
    api: {
        input: { target: './output/openapi.yaml' },
        output: {
            workspace: './output',
            target: '.',
            schemas: './schemas',
            client: 'axios-functions',
            mode: 'split',
            mock: true,
        },
    },
});
