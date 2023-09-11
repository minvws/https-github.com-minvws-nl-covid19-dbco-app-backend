import type { MockedFunctions } from '..';
import type { EventBus } from '../../utils';

type MockEventBus<KnownEvents extends Record<string, unknown>> = MockedFunctions<EventBus<KnownEvents>> & {
    mockClear: () => void;
};

export function createMockedEventBus<KnownEvents extends Record<string, unknown> = any>(): MockEventBus<KnownEvents> {
    const mockEventBus: MockEventBus<KnownEvents> = {
        $on: vi.fn(),
        $off: vi.fn(),
        $emit: vi.fn(),
        mockClear: () => {
            mockEventBus.$on.mockClear();
            mockEventBus.$off.mockClear();
            mockEventBus.$emit.mockClear();
        },
    };

    return mockEventBus;
}
