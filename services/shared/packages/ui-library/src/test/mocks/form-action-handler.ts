import type { MockedFunctions } from '..';
import type { FormActionHandler, FormData } from '../../json-forms/types';

type MockFormActionHandler = MockedFunctions<FormActionHandler> & {
    mockClear: () => void;
};

export function createMockFormActionHandler(): MockFormActionHandler {
    const mockFormActionHandler: MockFormActionHandler = {
        create: vi.fn(),
        read: vi.fn(),
        update: vi.fn((config, data) => Promise.resolve(data as FormData)),
        delete: vi.fn(),
        mockClear: () => {
            mockFormActionHandler.create.mockClear();
            mockFormActionHandler.read.mockClear();
            mockFormActionHandler.update.mockClear();
            mockFormActionHandler.delete.mockClear();
        },
    };

    return mockFormActionHandler;
}
