import { setupServer } from 'msw/node';
import { handlers } from './handlers';

export const mockServer = setupServer(...handlers);

export const enableMockServer = () => {
    // Establish API mocking before all tests.
    beforeAll(() => mockServer.listen({ onUnhandledRequest: 'warn' }));

    // Reset any request handlers that we may add during the tests,
    // so they don't affect other tests.
    afterEach(() => mockServer.resetHandlers());

    // Clean up after the tests are finished.
    afterAll(() => mockServer.close());

    return mockServer;
};
